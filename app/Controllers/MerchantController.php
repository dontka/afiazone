<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Merchant;
use App\Models\MerchantShippingInfo;
use App\Models\MerchantFees;
use App\Services\MerchantService;
use App\Services\KycService;
use Exception;

class MerchantController extends BaseController
{
    /**
     * Afficher le profil public d'un marchand
     * GET /api/merchants/{id}
     */
    public function show(): void
    {
        try {
            $id = $this->getPathParam('id');
            
            if (!$id) {
                $this->error('Merchant ID is required', 400);
                return;
            }
            
            $merchant = Merchant::find((int)$id);
            if (!$merchant) {
                $this->error('Merchant not found', 404);
                return;
            }
            
            // Vérifier le statut (seuls les actifs sont visibles)
            if ($merchant->status !== 'active') {
                $this->error('Merchant not available', 404);
                return;
            }
            
            $merchant->load('user', 'shippingInfo');
            
            $this->success([
                'id' => $merchant->id,
                'user_id' => $merchant->user_id,
                'business_name' => $merchant->business_name,
                'business_type' => $merchant->business_type,
                'description' => $merchant->description,
                'logo_url' => $merchant->logo_url,
                'cover_image_url' => $merchant->cover_image_url,
                'rating' => $merchant->rating,
                'total_reviews' => $merchant->total_reviews,
                'total_sales' => $merchant->total_sales,
                'status' => $merchant->status,
                'verification_date' => $merchant->verification_date,
                'tier_id' => $merchant->tier_id,
                'created_at' => $merchant->created_at,
                'user' => $merchant->user ? [
                    'id' => $merchant->user->id,
                    'email' => $merchant->user->email,
                    'first_name' => $merchant->user->first_name,
                    'last_name' => $merchant->user->last_name,
                ] : null,
                'shipping_info' => $merchant->shippingInfo ? [
                    'warehouse_address' => $merchant->shippingInfo->warehouse_address,
                    'warehouse_city' => $merchant->shippingInfo->warehouse_city,
                    'warehouse_country' => $merchant->shippingInfo->warehouse_country,
                    'return_policy' => $merchant->shippingInfo->return_policy,
                    'processing_time_days' => $merchant->shippingInfo->processing_time_days,
                    'accepts_cash_on_delivery' => (bool)$merchant->shippingInfo->accepts_cash_on_delivery,
                    'accepts_wallet_payment' => (bool)$merchant->shippingInfo->accepts_wallet_payment,
                ] : null,
            ]);
        } catch (Exception $e) {
            $this->error('Failed to fetch merchant: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer mon profil marchand (merchant seulement)
     * GET /api/me/merchant
     */
    public function me(): void
    {
        try {
            $this->requireAuth();
            $this->requireRole(['merchant']);
            
            $userId = $this->getCurrentUserId();
            $merchant = Merchant::where('user_id', $userId)->first();
            
            if (!$merchant) {
                $this->error('Merchant profile not found', 404);
                return;
            }
            
            $merchant->load('user', 'shippingInfo', 'fees');
            
            $this->success([
                'id' => $merchant->id,
                'user_id' => $merchant->user_id,
                'business_name' => $merchant->business_name,
                'business_type' => $merchant->business_type,
                'description' => $merchant->description,
                'logo_url' => $merchant->logo_url,
                'cover_image_url' => $merchant->cover_image_url,
                'rating' => $merchant->rating,
                'total_reviews' => $merchant->total_reviews,
                'total_sales' => $merchant->total_sales,
                'status' => $merchant->status,
                'tier_id' => $merchant->tier_id,
                'verification_date' => $merchant->verification_date,
                'created_at' => $merchant->created_at,
                'updated_at' => $merchant->updated_at,
                'shipping_info' => $merchant->shippingInfo,
                'fees' => $merchant->fees,
            ]);
        } catch (Exception $e) {
            $this->error('Failed to fetch merchant profile: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Enregistrer un nouveau profil marchand
     * POST /api/merchants
     */
    public function register(): void
    {
        try {
            $this->requireAuth();
            
            $data = $this->getJsonBody([
                'business_name' => 'required|string|max:255',
                'business_type' => 'required|in:wholesaler,producer,retailer',
                'description' => 'nullable|string',
                'warehouse_address' => 'required|string|max:512',
                'warehouse_city' => 'required|string|max:100',
                'warehouse_country' => 'required|string|max:100',
            ]);
            
            if (!$data) {
                return;
            }
            
            $userId = $this->getCurrentUserId();
            
            // Vérifier qu'aucun merchant n'existe pour cet utilisateur
            $existing = Merchant::where('user_id', $userId)->first();
            if ($existing) {
                $this->error('Merchant profile already exists for this user', 409);
                return;
            }
            
            // Créer le merchant
            $merchantService = new MerchantService();
            $merchant = $merchantService->createMerchant($userId, $data);
            
            $this->success([
                'id' => $merchant->id,
                'user_id' => $merchant->user_id,
                'business_name' => $merchant->business_name,
                'business_type' => $merchant->business_type,
                'status' => $merchant->status,
                'tier_id' => $merchant->tier_id,
                'created_at' => $merchant->created_at,
            ], 'Merchant profile created successfully', 201);
        } catch (Exception $e) {
            $this->error('Failed to register merchant: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mettre à jour mon profil marchand
     * PUT /api/me/merchant
     */
    public function updateProfile(): void
    {
        try {
            $this->requireAuth();
            $this->requireRole(['merchant']);
            
            $data = $this->getJsonBody([
                'business_name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'logo_url' => 'nullable|string|url',
                'cover_image_url' => 'nullable|string|url',
            ]);
            
            if ($data === null) {
                return;
            }
            
            $userId = $this->getCurrentUserId();
            $merchant = Merchant::where('user_id', $userId)->first();
            
            if (!$merchant) {
                $this->error('Merchant profile not found', 404);
                return;
            }
            
            $merchantService = new MerchantService();
            $merchant = $merchantService->updateMerchant($merchant->id, $data);
            
            $this->success([
                'id' => $merchant->id,
                'business_name' => $merchant->business_name,
                'description' => $merchant->description,
                'logo_url' => $merchant->logo_url,
                'cover_image_url' => $merchant->cover_image_url,
                'updated_at' => $merchant->updated_at,
            ], 'Merchant profile updated successfully');
        } catch (Exception $e) {
            $this->error('Failed to update merchant: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Afficher le dashboard marchand
     * GET /api/me/merchant/dashboard
     */
    public function dashboard(): void
    {
        try {
            $this->requireAuth();
            $this->requireRole(['merchant']);
            
            $userId = $this->getCurrentUserId();
            $merchant = Merchant::where('user_id', $userId)->first();
            
            if (!$merchant) {
                $this->error('Merchant profile not found', 404);
                return;
            }
            
            $merchantService = new MerchantService();
            $dashboard = $merchantService->getDashboard($merchant->id);
            
            $this->success($dashboard);
        } catch (Exception $e) {
            $this->error('Failed to fetch dashboard: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Afficher les informations de tier marchand
     * GET /api/me/merchant/tier
     */
    public function tierInfo(): void
    {
        try {
            $this->requireAuth();
            $this->requireRole(['merchant']);
            
            $userId = $this->getCurrentUserId();
            $merchant = Merchant::where('user_id', $userId)->first();
            
            if (!$merchant) {
                $this->error('Merchant profile not found', 404);
                return;
            }
            
            $merchantService = new MerchantService();
            $tierInfo = $merchantService->getTierInfo($merchant->id);
            
            $this->success($tierInfo);
        } catch (Exception $e) {
            $this->error('Failed to fetch tier info: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mettre à jour les infos de livraison
     * POST /api/merchants/{id}/shipping-info
     */
    public function updateShippingInfo(): void
    {
        try {
            $this->requireAuth();
            
            $id = $this->getPathParam('id');
            if (!$id) {
                $this->error('Merchant ID is required', 400);
                return;
            }
            
            $userId = $this->getCurrentUserId();
            $merchant = Merchant::find((int)$id);
            
            if (!$merchant) {
                $this->error('Merchant not found', 404);
                return;
            }
            
            // Vérifier l'ownership ou admin
            if ($merchant->user_id !== $userId && !$this->hasRole('admin')) {
                $this->error('Unauthorized', 403);
                return;
            }
            
            $data = $this->getJsonBody([
                'warehouse_address' => 'required|string|max:512',
                'warehouse_city' => 'required|string|max:100',
                'warehouse_country' => 'required|string|max:100',
                'return_policy' => 'nullable|string',
                'processing_time_days' => 'nullable|integer|min:1|max:30',
                'accepts_cash_on_delivery' => 'nullable|boolean',
                'accepts_wallet_payment' => 'nullable|boolean',
            ]);
            
            if ($data === null) {
                return;
            }
            
            $shippingInfo = MerchantShippingInfo::where('merchant_id', $id)->first();
            if (!$shippingInfo) {
                $shippingInfo = new MerchantShippingInfo();
                $shippingInfo->merchant_id = (int)$id;
            }
            
            $shippingInfo->fill($data);
            $shippingInfo->save();
            
            $this->success([
                'merchant_id' => $shippingInfo->merchant_id,
                'warehouse_address' => $shippingInfo->warehouse_address,
                'warehouse_city' => $shippingInfo->warehouse_city,
                'warehouse_country' => $shippingInfo->warehouse_country,
                'return_policy' => $shippingInfo->return_policy,
                'processing_time_days' => $shippingInfo->processing_time_days,
                'accepts_cash_on_delivery' => (bool)$shippingInfo->accepts_cash_on_delivery,
                'accepts_wallet_payment' => (bool)$shippingInfo->accepts_wallet_payment,
                'updated_at' => $shippingInfo->updated_at,
            ], 'Shipping info updated successfully');
        } catch (Exception $e) {
            $this->error('Failed to update shipping info: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mettre à jour les frais du marchand (admin seulement)
     * POST /api/merchants/{id}/fees
     */
    public function updateFees(): void
    {
        try {
            $this->requireAuth();
            $this->requireRole(['admin']);
            
            $id = $this->getPathParam('id');
            if (!$id) {
                $this->error('Merchant ID is required', 400);
                return;
            }
            
            $merchant = Merchant::find((int)$id);
            if (!$merchant) {
                $this->error('Merchant not found', 404);
                return;
            }
            
            $data = $this->getJsonBody([
                'commission_percent' => 'required|numeric|min:0|max:100',
                'return_fee_percent' => 'nullable|numeric|min:0|max:100',
                'refund_processing_days' => 'nullable|integer|min:1|max:30',
            ]);
            
            if ($data === null) {
                return;
            }
            
            $fees = MerchantFees::where('merchant_id', $id)->first();
            if (!$fees) {
                $fees = new MerchantFees();
                $fees->merchant_id = (int)$id;
            }
            
            $fees->fill($data);
            $fees->save();
            
            $this->success([
                'merchant_id' => $fees->merchant_id,
                'commission_percent' => (float)$fees->commission_percent,
                'return_fee_percent' => (float)$fees->return_fee_percent,
                'refund_processing_days' => $fees->refund_processing_days,
                'updated_at' => $fees->updated_at,
            ], 'Merchant fees updated successfully');
        } catch (Exception $e) {
            $this->error('Failed to update fees: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Lister tous les marchands (admin seulement)
     * GET /api/admin/merchants
     */
    public function adminList(): void
    {
        try {
            $this->requireAuth();
            $this->requireRole(['admin']);
            
            $page = (int)($this->getQueryParam('page') ?? 1);
            $perPage = (int)($this->getQueryParam('per_page') ?? 15);
            $status = $this->getQueryParam('status');
            $tier = $this->getQueryParam('tier_id');
            
            $query = Merchant::query();
            
            if ($status) {
                $query->where('status', $status);
            }
            
            if ($tier) {
                $query->where('tier_id', (int)$tier);
            }
            
            $total = $query->count();
            $merchants = $query
                ->orderByDesc('created_at')
                ->limit($perPage)
                ->offset(($page - 1) * $perPage)
                ->get();
            
            $merchants->load('user', 'shippingInfo', 'fees');
            
            $data = [];
            foreach ($merchants as $merchant) {
                $data[] = [
                    'id' => $merchant->id,
                    'user_id' => $merchant->user_id,
                    'business_name' => $merchant->business_name,
                    'business_type' => $merchant->business_type,
                    'rating' => (float)$merchant->rating,
                    'total_sales' => (float)$merchant->total_sales,
                    'status' => $merchant->status,
                    'tier_id' => $merchant->tier_id,
                    'created_at' => $merchant->created_at,
                    'user' => $merchant->user ? [
                        'email' => $merchant->user->email,
                        'first_name' => $merchant->user->first_name,
                        'last_name' => $merchant->user->last_name,
                    ] : null,
                ];
            }
            
            $this->success([
                'merchants' => $data,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage,
                    'pages' => ceil($total / $perPage),
                ],
            ]);
        } catch (Exception $e) {
            $this->error('Failed to list merchants: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mettre à jour le statut d'un marchand (admin seulement)
     * PUT /api/admin/merchants/{id}/status
     */
    public function updateStatus(): void
    {
        try {
            $this->requireAuth();
            $this->requireRole(['admin']);
            
            $id = $this->getPathParam('id');
            if (!$id) {
                $this->error('Merchant ID is required', 400);
                return;
            }
            
            $merchant = Merchant::find((int)$id);
            if (!$merchant) {
                $this->error('Merchant not found', 404);
                return;
            }
            
            $data = $this->getJsonBody([
                'status' => 'required|in:active,suspended,banned',
            ]);
            
            if ($data === null) {
                return;
            }
            
            $merchant->status = $data['status'];
            $merchant->save();
            
            $this->success([
                'id' => $merchant->id,
                'status' => $merchant->status,
                'updated_at' => $merchant->updated_at,
            ], 'Merchant status updated successfully');
        } catch (Exception $e) {
            $this->error('Failed to update merchant status: ' . $e->getMessage(), 500);
        }
    }
}
