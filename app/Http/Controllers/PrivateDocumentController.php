<?php

namespace App\Http\Controllers;

use App\Models\SecurityAgency;
use App\Models\Warehouse;
use App\Models\WarehouseRequest;
use App\Models\Box;
use App\Models\DeliveryStop;
use App\Models\Equipment;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PrivateDocumentController extends Controller
{
    public function show(Request $request, string $path)
    {
        abort_if(str_contains($path, '..') || str_starts_with($path, '/') || str_starts_with($path, '\\') || str_contains($path, '\\'), Response::HTTP_BAD_REQUEST);

        $user = $request->user();
        abort_unless($user && $this->canAccess($user, $path), Response::HTTP_FORBIDDEN);
        abort_unless(Storage::disk('private_uploads')->exists($path), Response::HTTP_NOT_FOUND);

        return Storage::disk('private_uploads')->response($path);
    }

    private function canAccess($user, string $path): bool
    {
        if (($user->role ?? null) === 'admin') {
            return $this->documentExistsInRecords($path);
        }

        $ownsWarehouseDocument = Warehouse::where('user_id', $user->id)
            ->where(function ($query) use ($path) {
                $query->where('ownership_document', $path)
                    ->orWhere('tax_document', $path)
                    ->orWhere('fire_safety_document', $path)
                    ->orWhere('building_approval_document', $path);
            })
            ->exists();

        if ($ownsWarehouseDocument) {
            return true;
        }

        $ownsEquipmentDocument = Equipment::where(function ($query) use ($path) {
                $this->whereEquipmentDocumentPath($query, $path);
            })
            ->where(function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhere('user_id', $user->id);
            })
            ->exists();

        if ($ownsEquipmentDocument) {
            return true;
        }

        $canAccessWarehouseRequestDocument = WarehouseRequest::where(function ($query) use ($path) {
                $this->whereWarehouseRequestDocumentPath($query, $path);
            })
            ->where(function ($query) use ($user) {
                $query->where('client_id', $user->id)
                    ->orWhereHas('warehouse', function ($warehouseQuery) use ($user) {
                        $this->whereWarehouseOwner($warehouseQuery, $user->id);
                    });
            })
            ->exists();

        if ($canAccessWarehouseRequestDocument) {
            return true;
        }

        return SecurityAgency::where('user_id', $user->id)
            ->where(function ($query) use ($path) {
                $query->where('registration_certificate_path', $path)
                    ->orWhere('license_certificate_path', $path)
                    ->orWhere('pan_vat_certificate_path', $path);
            })
            ->exists()
            || $this->canAccessBoxDocument($user, $path)
            || $this->canAccessDeliveryStopDocument($user, $path)
            || $this->canAccessVehicleDocument($user, $path);
    }

    private function documentExistsInRecords(string $path): bool
    {
        return Warehouse::where(function ($query) use ($path) {
                $query->where('ownership_document', $path)
                    ->orWhere('tax_document', $path)
                    ->orWhere('fire_safety_document', $path)
                    ->orWhere('building_approval_document', $path);
            })
            ->exists()
            || SecurityAgency::where(function ($query) use ($path) {
                $query->where('registration_certificate_path', $path)
                    ->orWhere('license_certificate_path', $path)
                    ->orWhere('pan_vat_certificate_path', $path);
            })
            ->exists()
            || Box::where(function ($query) use ($path) {
                $this->whereBoxDocumentPath($query, $path);
            })
            ->exists()
            || Equipment::where(function ($query) use ($path) {
                $this->whereEquipmentDocumentPath($query, $path);
            })
            ->exists()
            || WarehouseRequest::where(function ($query) use ($path) {
                $this->whereWarehouseRequestDocumentPath($query, $path);
            })
            ->exists()
            || DeliveryStop::where('invoice_document', $path)
            ->exists()
            || Vehicle::where(function ($query) use ($path) {
                $this->whereVehicleDocumentPath($query, $path);
            })
            ->exists();
    }

    private function canAccessBoxDocument($user, string $path): bool
    {
        return Box::where(function ($query) use ($path) {
                $this->whereBoxDocumentPath($query, $path);
            })
            ->where(function ($query) use ($user) {
                $query->where('client_id', $user->id)
                    ->orWhereHas('warehouse', function ($warehouseQuery) use ($user) {
                        $this->whereWarehouseOwner($warehouseQuery, $user->id);
                    });
            })
            ->exists();
    }

    private function canAccessDeliveryStopDocument($user, string $path): bool
    {
        return DeliveryStop::where('invoice_document', $path)
            ->whereHas('dispatchOrder', function ($query) use ($user) {
                $query->where('client_id', $user->id)
                    ->orWhere('driver_id', $user->id);
            })
            ->exists();
    }

    private function canAccessVehicleDocument($user, string $path): bool
    {
        return Vehicle::where(function ($query) use ($path) {
                $this->whereVehicleDocumentPath($query, $path);
            })
            ->where(function ($query) use ($user) {
                $query->where('driver_id', $user->id)
                    ->orWhere('user_id', $user->id);
            })
            ->exists();
    }

    private function whereBoxDocumentPath($query, string $path): void
    {
        $query->where('invoice_document', $path)
            ->orWhere('packing_list_document', $path)
            ->orWhere('insurance_document', $path)
            ->orWhereJsonContains('other_documents', $path);
    }

    private function whereEquipmentDocumentPath($query, string $path): void
    {
        $query->where('registration_doc', $path)
            ->orWhere('insurance_doc', $path);
    }

    private function whereWarehouseRequestDocumentPath($query, string $path): void
    {
        $query->where('invoice_path', $path)
            ->orWhere('packing_list_path', $path)
            ->orWhere('insurance_path', $path);
    }

    private function whereVehicleDocumentPath($query, string $path): void
    {
        $query->where('insurance_file_path', $path)
            ->orWhere('fitness_file_path', $path)
            ->orWhere('pollution_file_path', $path)
            ->orWhere('permit_file_path', $path)
            ->orWhere('blue_book_file_path', $path);
    }

    private function whereWarehouseOwner($query, int $userId): void
    {
        $query->where('user_id', $userId);

        if (Schema::hasColumn('warehouses', 'owner_id')) {
            $query->orWhere('owner_id', $userId);
        }
    }
}
