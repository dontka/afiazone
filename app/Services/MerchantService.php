<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Merchant;
use App\Models\MerchantShippingInfo;
use App\Models\MerchantFees;
use Exception;

class MerchantService extends BaseService
{
    /**
     * Créer un nouveau profil marchand
     */
    public function createMerchant(int $userId, array $data): Merchant
    {
        try {
            // Vérifier qu'aucun marchand n'existe pour cet utilisateur
            $existing = Merchant::query()->where('user_id', $userId)->first();
            if ($existing) {
                throw new Exception('Merchant profile already exists for this user');
            }
            
            $merchant = new Merchant();
            $merchant->user_id = $userId;
            $merchant->business_name = $data['business_name'];
            $merchant->business_type = $data['business_type'] ?? 'retailer';
            $merchant->description = $data['description'] ?? '';
            $merchant->status = 'active';
            $merchant->tier_id = 1; // Verified par défaut
            $merchant->save();
            
            // Créer les infos de livraison
            if (isset($data['warehouse_address'])) {
                $shippingInfo = new MerchantShippingInfo();
                $shippingInfo->merchant_id = $merchant->id;
                $shippingInfo->warehouse_address = $data['warehouse_address'];
                $shippingInfo->warehouse_city = $data['warehouse_city'] ?? '';
                $shippingInfo->warehouse_country = $data['warehouse_country'] ?? '';
                $shippingInfo->return_policy = $data['return_policy'] ?? '';
                $shippingInfo->processing_time_days = $data['processing_time_days'] ?? 3;
                $shippingInfo->accepts_cash_on_delivery = $data['accepts_cash_on_delivery'] ?? true;
                $shippingInfo->accepts_wallet_payment = $data['accepts_wallet_payment'] ?? true;
                $shippingInfo->save();
            }
            
            // Créer la configuration des frais
            $fees = new MerchantFees();
            $fees->merchant_id = $merchant->id;
            $fees->commission_percent = $data['commission_percent'] ?? 10.0;
            $fees->return_fee_percent = $data['return_fee_percent'] ?? 2.0;
            $fees->refund_processing_days = $data['refund_processing_days'] ?? 5;
            $fees->save();
            
            return $merchant;
        } catch (Exception $e) {
            throw new Exception('Failed to create merchant: ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour le profil marchand
     */
    public function updateMerchant(int $merchantId, array $data): Merchant
    {
        try {
            $merchant = Merchant::find($merchantId);
            if (!$merchant) {
                throw new Exception('Merchant not found');
            }
            
            $merchant->fill($data);
            $merchant->save();
            
            return $merchant;
        } catch (Exception $e) {
            throw new Exception('Failed to update merchant: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir le dashboard marchand
     */
    public function getDashboard(int $merchantId): array
    {
        try {
            $merchant = Merchant::find($merchantId);
            if (!$merchant) {
                throw new Exception('Merchant not found');
            }
            
            // TODO: Récupérer les statistiques réelles depuis les tables de commandes
            // Pour maintenant, retourner les valeurs du modèle
            
            return [
                'id' => $merchant->id,
                'business_name' => $merchant->business_name,
                'status' => $merchant->status,
                'rating' => (float)$merchant->rating,
                'total_reviews' => (int)$merchant->total_reviews,
                'total_sales' => (float)$merchant->total_sales,
                'statistics' => [
                    'total_orders' => 0, // TODO
                    'pending_orders' => 0, // TODO
                    'order_value_today' => 0.0, // TODO
                    'order_value_this_month' => 0.0, // TODO
                    'products_count' => 0, // TODO
                    'active_products' => 0, // TODO
                ],
            ];
        } catch (Exception $e) {
            throw new Exception('Failed to get dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir les informations de tier
     */
    public function getTierInfo(int $merchantId): array
    {
        try {
            $merchant = Merchant::find($merchantId);
            if (!$merchant) {
                throw new Exception('Merchant not found');
            }
            
            $currentTier = $merchant->tier_id;
            
            // Conditions pour upgrade
            $conditions = [
                1 => ['name' => 'Verified', 'requirements' => ['kyc_approved' => true]],
                2 => ['name' => 'Premium', 'requirements' => ['min_sales' => 10000, 'min_rating' => 4.0, 'min_reviews' => 10]],
                3 => ['name' => 'Gold', 'requirements' => ['min_sales' => 50000, 'min_rating' => 4.5, 'min_reviews' => 50]],
                4 => ['name' => 'Diamond', 'requirements' => ['min_sales' => 250000, 'min_rating' => 4.7, 'min_reviews' => 100]],
            ];
            
            return [
                'current_tier_id' => $currentTier,
                'current_tier' => $conditions[$currentTier]['name'] ?? 'Unknown',
                'next_tier_id' => $currentTier < 4 ? $currentTier + 1 : null,
                'next_tier' => $currentTier < 4 ? $conditions[$currentTier + 1]['name'] ?? 'Unknown' : null,
                'current_metrics' => [
                    'total_sales' => (float)$merchant->total_sales,
                    'rating' => (float)$merchant->rating,
                    'total_reviews' => (int)$merchant->total_reviews,
                ],
                'upgrade_requirements' => $currentTier < 4 ? $conditions[$currentTier + 1]['requirements'] : null,
            ];
        } catch (Exception $e) {
            throw new Exception('Failed to get tier info: ' . $e->getMessage());
        }
    }

    /**
     * Vérifier si un marchand peut upgraded vers le tier suivant
     */
    public function canUpgradeTier(int $merchantId): bool
    {
        try {
            $merchant = Merchant::find($merchantId);
            if (!$merchant) {
                throw new Exception('Merchant not found');
            }
            
            if ($merchant->tier_id >= 4) {
                return false;
            }
            
            // Vérifier les conditions d'upgrade
            $nextTier = $merchant->tier_id + 1;
            
            switch ($nextTier) {
                case 2: // Premium
                    return (float)$merchant->total_sales >= 10000 &&
                           (float)$merchant->rating >= 4.0 &&
                           (int)$merchant->total_reviews >= 10;
                
                case 3: // Gold
                    return (float)$merchant->total_sales >= 50000 &&
                           (float)$merchant->rating >= 4.5 &&
                           (int)$merchant->total_reviews >= 50;
                
                case 4: // Diamond
                    return (float)$merchant->total_sales >= 250000 &&
                           (float)$merchant->rating >= 4.7 &&
                           (int)$merchant->total_reviews >= 100;
                
                default:
                    return false;
            }
        } catch (Exception $e) {
            throw new Exception('Failed to check upgrade eligibility: ' . $e->getMessage());
        }
    }

    /**
     * Upgrade le tier d'un marchand
     */
    public function upgradeTier(int $merchantId): bool
    {
        try {
            if (!$this->canUpgradeTier($merchantId)) {
                throw new Exception('Merchant does not meet upgrade requirements');
            }
            
            $merchant = Merchant::find($merchantId);
            if (!$merchant) {
                throw new Exception('Merchant not found');
            }
            
            $merchant->tier_id = $merchant->tier_id + 1;
            $merchant->save();
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to upgrade tier: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir les frais du marchand
     */
    public function getFees(int $merchantId): ?array
    {
        try {
            $fees = MerchantFees::query()->where('merchant_id', $merchantId)->first();
            
            if (!$fees) {
                return null;
            }
            
            return [
                'merchant_id' => $fees->merchant_id,
                'commission_percent' => (float)$fees->commission_percent,
                'return_fee_percent' => (float)$fees->return_fee_percent,
                'refund_processing_days' => (int)$fees->refund_processing_days,
            ];
        } catch (Exception $e) {
            throw new Exception('Failed to get fees: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir les infos de livraison du marchand
     */
    public function getShippingInfo(int $merchantId): ?array
    {
        try {
            $info = MerchantShippingInfo::query()->where('merchant_id', $merchantId)->first();
            
            if (!$info) {
                return null;
            }
            
            return [
                'merchant_id' => $info->merchant_id,
                'warehouse_address' => $info->warehouse_address,
                'warehouse_city' => $info->warehouse_city,
                'warehouse_country' => $info->warehouse_country,
                'return_policy' => $info->return_policy,
                'processing_time_days' => (int)$info->processing_time_days,
                'accepts_cash_on_delivery' => (bool)$info->accepts_cash_on_delivery,
                'accepts_wallet_payment' => (bool)$info->accepts_wallet_payment,
            ];
        } catch (Exception $e) {
            throw new Exception('Failed to get shipping info: ' . $e->getMessage());
        }
    }
}
