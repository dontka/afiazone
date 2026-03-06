<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Shipment;
use App\Models\ShipmentTrackingLog;
use App\Models\DeliveryPersonnel;

class DeliveryService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function createShipment(int $orderId, array $data = []): Shipment
    {
        $shipment = Shipment::create([
            'order_id' => $orderId,
            'delivery_provider_id' => $data['provider_id'] ?? null,
            'delivery_personnel_id' => $data['personnel_id'] ?? null,
            'tracking_number' => Shipment::generateTrackingNumber(),
            'delivery_code' => Shipment::generateDeliveryCode(),
            'estimated_delivery_at' => $data['estimated_delivery_at'] ?? null,
            'shipping_cost' => $data['shipping_cost'] ?? 0,
            'status' => 'pending',
        ]);

        $this->addTrackingLog($shipment->id, 'pending', 'Shipment created');
        $this->log('Shipment created', ['shipment_id' => $shipment->id, 'order_id' => $orderId]);

        return $shipment;
    }

    public function updateStatus(int $shipmentId, string $status, string $notes = '', ?string $location = null): bool
    {
        $shipment = Shipment::find($shipmentId);
        if (!$shipment) {
            return false;
        }

        $updateData = ['status' => $status];
        if ($status === 'picked_up') {
            $updateData['picked_up_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'delivered') {
            $updateData['delivered_at'] = date('Y-m-d H:i:s');
        }

        $shipment->update($updateData);
        $this->addTrackingLog($shipmentId, $status, $notes, $location);

        $this->log('Shipment status updated', ['shipment_id' => $shipmentId, 'status' => $status]);
        return true;
    }

    public function assignPersonnel(int $shipmentId, int $personnelId): bool
    {
        $shipment = Shipment::find($shipmentId);
        $personnel = DeliveryPersonnel::find($personnelId);

        if (!$shipment || !$personnel) {
            return false;
        }

        $shipment->update([
            'delivery_personnel_id' => $personnelId,
            'status' => 'assigned',
        ]);

        $personnel->update(['is_available' => false]);

        $this->addTrackingLog($shipmentId, 'assigned', "Assigned to delivery personnel #{$personnelId}");
        return true;
    }

    public function getByTrackingNumber(string $trackingNumber): ?Shipment
    {
        return Shipment::findByTrackingNumber($trackingNumber);
    }

    public function getTrackingHistory(int $shipmentId): array
    {
        $shipment = Shipment::find($shipmentId);
        if (!$shipment) {
            return [];
        }
        return $shipment->getTrackingLogs();
    }

    private function addTrackingLog(int $shipmentId, string $status, string $notes = '', ?string $location = null): void
    {
        ShipmentTrackingLog::create([
            'shipment_id' => $shipmentId,
            'status' => $status,
            'notes' => $notes,
            'location' => $location,
        ]);
    }
}
