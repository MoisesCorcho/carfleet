<?php

declare(strict_types=1);

namespace App\Actions\Trips;

use App\DTOs\Trips\CaptureSignatureDTO;
use App\Exceptions\Trips\InvalidSignatureDataException;
use App\Exceptions\Trips\InvalidTripStateException;
use App\Exceptions\Trips\TripImmutableException;
use App\Models\DigitalSignature;
use App\Models\Trip;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CaptureTripSignatureAction
{
    /**
     * Capture digital signature from canvas, decode base64, save PNG and persist model.
     *
     * @throws TripImmutableException
     * @throws InvalidTripStateException
     * @throws InvalidSignatureDataException
     */
    public function __invoke(CaptureSignatureDTO $dto): DigitalSignature
    {
        $signerName = trim($dto->signerName);
        if ($signerName === '') {
            throw InvalidSignatureDataException::emptySignerName();
        }

        $rawBase64 = trim($dto->signatureBase64);
        if ($rawBase64 === '') {
            throw InvalidSignatureDataException::emptySignature();
        }

        // Strip data URI scheme prefix if present (e.g. data:image/png;base64,...)
        if (str_contains($rawBase64, ',')) {
            $parts = explode(',', $rawBase64, 2);
            $rawBase64 = $parts[1] ?? '';
        }

        $decodedImage = base64_decode($rawBase64, true);
        if ($decodedImage === false || strlen($decodedImage) === 0) {
            throw InvalidSignatureDataException::invalidFormat();
        }

        return DB::transaction(function () use ($dto, $signerName, $decodedImage): DigitalSignature {
            /** @var Trip $trip */
            $trip = Trip::where('id', $dto->tripId)->lockForUpdate()->firstOrFail();

            if ($trip->isImmutable()) {
                throw TripImmutableException::forTrip($trip->code, $trip->status);
            }

            if (! $trip->isCompleted()) {
                throw InvalidTripStateException::cannotSign($trip->code, $trip->status);
            }

            // If a previous signature exists on this trip, clean up its file
            /** @var DigitalSignature|null $existingSignature */
            $existingSignature = DigitalSignature::where('trip_id', $trip->id)->first();
            if ($existingSignature && $existingSignature->signature_path) {
                if (Storage::disk('public')->exists($existingSignature->signature_path)) {
                    Storage::disk('public')->delete($existingSignature->signature_path);
                }
            }

            $fileName = 'signature_'.$trip->id.'_'.Str::uuid().'.png';
            $filePath = 'evidences/signatures/'.$fileName;

            Storage::disk('public')->put($filePath, $decodedImage);

            /** @var DigitalSignature $signature */
            $signature = DigitalSignature::updateOrCreate(
                ['trip_id' => $trip->id],
                [
                    'signer_name' => $signerName,
                    'signature_path' => $filePath,
                    'signed_at' => now(),
                ]
            );

            return $signature;
        });
    }
}
