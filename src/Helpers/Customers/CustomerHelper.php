<?php

namespace Makiomar\WooOrderDashboard\Helpers\Customers;

use Makiomar\WooOrderDashboard\Helpers\BaseHelper;

class CustomerHelper extends BaseHelper
{
    /**
     * Get customer by ID
     *
     * @param int $customerId
     * @return array|null
     */
    public static function getCustomer($customerId)
    {
        return self::remember("woo_customer_{$customerId}", 3600, function () use ($customerId) {
            $customer = self::getConnection()
                ->table(self::getPrefix() . 'users as u')
                ->leftJoin(self::getPrefix() . 'usermeta as um', 'u.ID', '=', 'um.user_id')
                ->where('u.ID', $customerId)
                ->select('u.*', 'um.meta_key', 'um.meta_value')
                ->get();

            if ($customer->isEmpty()) {
                return null;
            }

            return self::transformCustomerData($customer);
        });
    }

    /**
     * Search customers
     *
     * @param string $search
     * @param int $limit
     * @return array
     */
    public static function searchCustomers($search, $limit = 20)
    {
        $customers = self::getConnection()
            ->table(self::getPrefix() . 'users as u')
            ->leftJoin(self::getPrefix() . 'usermeta as fn', function($join) {
                $join->on('u.ID', '=', 'fn.user_id')
                     ->where('fn.meta_key', 'first_name');
            })
            ->leftJoin(self::getPrefix() . 'usermeta as ln', function($join) {
                $join->on('u.ID', '=', 'ln.user_id')
                     ->where('ln.meta_key', 'last_name');
            })
            ->where(function($query) use ($search) {
                $query->where('u.user_email', 'like', "%{$search}%")
                      ->orWhere('fn.meta_value', 'like', "%{$search}%")
                      ->orWhere('ln.meta_value', 'like', "%{$search}%");
            })
            ->select('u.ID', 'u.user_email', 'fn.meta_value as first_name', 'ln.meta_value as last_name')
            ->limit($limit)
            ->get();

        return $customers->map(function($customer) {
            return [
                'id' => $customer->ID,
                'email' => $customer->user_email,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'full_name' => trim($customer->first_name . ' ' . $customer->last_name)
            ];
        })->toArray();
    }

    /**
     * Transform customer database data into a structured array
     *
     * @param \Illuminate\Support\Collection $data
     * @return array
     */
    protected static function transformCustomerData($data)
    {
        $customer = [
            'id' => $data->first()->ID,
            'email' => $data->first()->user_email,
            'username' => $data->first()->user_login,
            'registered' => $data->first()->user_registered,
            'meta' => []
        ];

        // Process meta data
        foreach ($data as $row) {
            if ($row->meta_key && $row->meta_value) {
                $customer['meta'][$row->meta_key] = $row->meta_value;
            }
        }

        // Extract common meta fields
        $customer['first_name'] = $customer['meta']['first_name'] ?? '';
        $customer['last_name'] = $customer['meta']['last_name'] ?? '';
        $customer['billing'] = [
            'first_name' => $customer['meta']['billing_first_name'] ?? '',
            'last_name' => $customer['meta']['billing_last_name'] ?? '',
            'company' => $customer['meta']['billing_company'] ?? '',
            'address_1' => $customer['meta']['billing_address_1'] ?? '',
            'address_2' => $customer['meta']['billing_address_2'] ?? '',
            'city' => $customer['meta']['billing_city'] ?? '',
            'state' => $customer['meta']['billing_state'] ?? '',
            'postcode' => $customer['meta']['billing_postcode'] ?? '',
            'country' => $customer['meta']['billing_country'] ?? '',
            'email' => $customer['meta']['billing_email'] ?? '',
            'phone' => $customer['meta']['billing_phone'] ?? ''
        ];

        return $customer;
    }
} 