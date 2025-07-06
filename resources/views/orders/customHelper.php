<?php

function getName($productID) {
    $prefix = config('app.db_prefix');
    $product_post = DB::table($prefix . 'posts')
                        ->where('ID', $productID)
                        ->first();
    
    return $product_post;
}

function getSKU($productID, $orderID) {
    $prefix = config('app.db_prefix');
    $bags = array("Small Bag/5", "Small Bag/3", "Small Bag/2", "not bag", "ﾙ心mall Bag/5");
    $product_postmeta = DB::table($prefix . 'postmeta')
                        ->where('post_id', $productID)
                        ->get();
                        
    if (isset($product_postmeta) && count($product_postmeta)>0) {
        foreach ($product_postmeta as $prod_postmeta) {
            foreach ($prod_postmeta as $index => $data) {
                if ($index === 'meta_key' && $data === '_sku') {
                    if (!in_array($prod_postmeta->meta_value, $bags)) {
                        $product_data['SKU'] = $prod_postmeta->meta_value;
                    } else {
                        $product_data['SKU'] = "Small Bag";
                    }
                }
                if ($index === 'meta_key' && $data === '_price') {
                    $product_data['price'] = $prod_postmeta->meta_value;
                }
                if ($index === 'meta_key' && $data === 'attribute_pa_%d8%a7%d9%84%d8%ad%d8%ac%d9%85') {
                    $product_data['size'] = $prod_postmeta->meta_value;
                } else {
                    $product_data['size'] = "";
                }
            }
        }
    } else {
        $product_data['SKU'] = "";
        $product_data['price'] = "";
        $product_data['size'] = "";
    }
    
    return $product_data;
}

function getPdata($productID, $orderID) {
    $prefix = config('app.db_prefix');
    
    $product = DB::table($prefix . 'posts')
                        ->where('ID', $productID)
                        ->first();
                        
    $product_data["title"] = $product->post_title;
    
    $product_data["sku"] = getPostMeta($product->post_parent, '_sku', 'string');
    
    return $product_data;
}

function getSize($productAttribute) {
    $prefix = config('app.db_prefix');
    $product_size = DB::table($prefix . 'terms')
                        ->where('slug', $productAttribute)
                        ->first();
                        
    if($product_size)
        return $product_size->name;
    else
        return $productAttribute;
}

function getTerms($productID) {
    $terms = array();
    $prefix = config('app.db_prefix');
    $product_terms = DB::table($prefix . 'term_relationships')
                        ->where('object_id', $productID)
                        ->get();
    
    foreach ($product_terms as $prod_terms) {
        $term_taxonomies = DB::table($prefix . 'term_taxonomy')
                            ->where([['term_taxonomy_id', $prod_terms->term_taxonomy_id], ['taxonomy', 'product_cat']])
                            ->get();
        
        foreach ($term_taxonomies as $term_taxonomy) {
            $terms_list = DB::table($prefix . 'terms')
                        ->where('term_id', $term_taxonomy->term_id)
                        ->first();
            $terms[] = $terms_list->name;
        }
    }
    
    return $terms;
}

function getOption($optionName) {
    $prefix = config('app.db_prefix');
    $option = DB::table($prefix . 'options')
                        ->where('option_name', $optionName)
                        ->first();
    
    return $option->option_value;
}

function getTotalWeight($orderId) {
    $prefix = config('app.db_prefix');
    $order_weight = DB::select("
                            SELECT 
                                SUM(qty.meta_value * pm.meta_value) AS total_weight
                            FROM 
                                ".$prefix."woocommerce_order_items oi
                            INNER JOIN 
                                ".$prefix."woocommerce_order_itemmeta qty 
                                ON oi.order_item_id = qty.order_item_id
                            INNER JOIN 
                                ".$prefix."woocommerce_order_itemmeta product_id 
                                ON oi.order_item_id = product_id.order_item_id
                            INNER JOIN 
                                ".$prefix."postmeta pm 
                                ON product_id.meta_value = pm.post_id
                            WHERE 
                                oi.order_id = ".$orderId."
                                AND oi.order_item_type = 'line_item'
                                AND qty.meta_key = '_ywcars_requested_qty'
                                AND product_id.meta_key = '_variation_id'
                                AND pm.meta_key = '_weight'
                            GROUP BY oi.order_id;
                        ");
    
    return $order_weight;
}

function getPostMeta($orderId, $metaKey, $type) {
    $prefix = config('app.db_prefix');
    $metaValue = DB::table($prefix . 'postmeta')
                        ->where([
                            ['post_id', $orderId],
                            ['meta_key', $metaKey]
                            ])
                        ->first();
                        
    if ($metaValue) {
        if ($type == 'float') {
            $metaValue = floatval($metaValue->meta_value);
        } else {
            $metaValue = $metaValue->meta_value;
        }
    } else {
        if ($type == 'float') {
            $metaValue = 0;
        } else {
            $metaValue = "";
        }
    }
    
    return $metaValue;
}

function getUserMeta($userId, $metaKey) {
    $prefix = config('app.db_prefix');
    $metaValue = DB::table($prefix . 'usermeta')
                        ->where([
                            ['user_id', $userId],
                            ['meta_key', $metaKey]
                            ])
                        ->first();
                        
    if ($metaValue) {
        $metaValue = $metaValue->meta_value;
    } else {
        $metaValue = "";
    }
    
    return $metaValue;
}

function updatePostMeta($orderId, $metaKey, $value) {
    $prefix = config('app.db_prefix');
    $metaValue = DB::table($prefix . 'postmeta')
                        ->where([
                            ['post_id', $orderId],
                            ['meta_key', $metaKey]
                            ])
                        ->first();
    if ($metaValue) {
        $metaValue->meta_value = $value;
        $metaValue->save();
    } else {
        DB::table($prefix . 'postmeta')->insert(
            array(
                'post_id'       =>   $orderId, 
                'meta_key'      =>   $metaKey,
                'meta_value'    =>   $value
            )
        );
    }
}

function updatePostStatus($postId, $status, $SAtime, $GMTtime) {
    $prefix = config('app.db_prefix');
    DB::table($prefix . 'posts')
            ->where('ID', $postId)
            ->update([
                'post_status' => $status,
                'post_modified' => $SAtime,
                'post_modified_gmt' => $GMTtime,
            ]);
}

function getRoles($userId) {
    $prefix = config('app.db_prefix');
    $metaValue = DB::table($prefix . 'usermeta')
                        ->where([
                            ['user_id', $userId],
                            ['meta_key', $prefix . 'capabilities']
                            ])
                        ->first();
                        
    return unserialize($metaValue->meta_value);
}

function getRoleName($role) {
    $prefix = config('app.db_prefix');
    $roleName = DB::table($prefix . 'wpnotif_subgroups')
                        ->where('list_type', $role)
                        ->first();
                        
    if ($roleName) {
        $roleName = $roleName->name;
    } else {
        $roleName = $role;
    }
                        
    return $roleName;
}

function getDisplayName($userId) {
    $prefix = config('app.db_prefix');
    $display_name = DB::table($prefix . 'users')
                        ->where('ID', $userId)
                        ->first();
                        
    if ($display_name) {
        $display_name = $display_name->display_name;
    } else {
        $display_name = "No display name";
    }
    
    return $display_name;
}

function getUsersList() {
    $prefix = config('app.db_prefix');
    $l_users = DB::table($prefix . 'users as u')
                    ->join($prefix . 'usermeta as um', 'u.ID', '=', 'um.user_id')
                    ->whereNotIn('u.ID', function($query) {
                        $query->select('wp_user')->from('users');
                    })
                    ->where('um.meta_key', $prefix . 'capabilities')
                    ->where(function($query) {
                        $query->where('um.meta_value', 'NOT LIKE', '%"customer"%');
                    })
                    ->groupBy('u.ID')
                    ->select('u.ID', 'u.display_name')
                    ->get();
                    
    return $l_users;
}



function getStatusList()
{
    $prefix = config('app.db_prefix');
    $wc_order_status = DB::table($prefix . 'posts')->select('post_name', 'post_title')
                            ->where('post_type', 'wc_order_status')
                            ->orderBy('menu_order', 'ASC')
                            ->get();

    return $wc_order_status;
}

function getCountryName($code) {
    $countryName = \App\Models\Country::where('code', $code)
                                    ->first();

    if ($countryName) {
        $countryName = $countryName->name;
    } else {
        $countryName = $code;
    }    

    return $countryName;
}

function getStatusName($status) {
    $status_name = substr($status, 3);
    $prefix = config('app.db_prefix');
    $wc_order_status = DB::table($prefix . 'posts')->select('post_title')
                            ->where([
                                    ['post_type', 'wc_order_status'],
                                    ['post_name', $status_name],
                                ])
                            ->first();

    return $wc_order_status->post_title;
}

function getSizeName($attribute) {
    $sizeName = DB::table('sizes')
                        ->where('slug', $attribute)
                        ->first();
    
    if ($sizeName) {
        return $sizeName->name;
    } else {
        return "";
    }
}

function getFees($orderID)
{
    $prefix = config('app.db_prefix');
    $order_fees = DB::select("
                            SELECT 
                                SUM(CAST(meta_fee.meta_value AS DECIMAL(10, 2))) AS total_fee_amount
                            FROM 
                                ".$prefix."woocommerce_order_items AS items
                            INNER JOIN 
                                ".$prefix."woocommerce_order_itemmeta AS meta_fee ON items.order_item_id = meta_fee.order_item_id
                            WHERE 
                                items.order_id = ".$orderID."
                                AND items.order_item_type = 'fee'
                                AND (items.order_item_name != 'Via wallet')
                                AND meta_fee.meta_key = '_line_total';
                        ");
                        
    if ($order_fees[0]->total_fee_amount != "") {
        return $order_fees[0]->total_fee_amount;
    } else {
        return "0.00";
    }
}

function getWallet($orderID)
{
    $prefix = config('app.db_prefix');
    $order_wallet = DB::select("
                            SELECT 
                                SUM(CAST(meta_fee.meta_value AS DECIMAL(10, 2))) AS total_wallet_amount
                            FROM 
                                ".$prefix."woocommerce_order_items AS items
                            INNER JOIN 
                                ".$prefix."woocommerce_order_itemmeta AS meta_fee ON items.order_item_id = meta_fee.order_item_id
                            WHERE 
                                items.order_id = ".$orderID."
                                AND items.order_item_type = 'fee'
                                AND items.order_item_name = 'Via wallet'
                                AND meta_fee.meta_key = '_line_total';
                        ");
                        
    if ($order_wallet[0]->total_wallet_amount != "") {
        return $order_wallet[0]->total_wallet_amount;
    } else {
        return "0.00";
    }
    
    
}

function getCoupons($orderID)
{
    $prefix = config('app.db_prefix');
    $order_coupons = DB::select("
                            SELECT 
                                oi.order_item_name AS coupon_code
                            FROM 
                                ".$prefix."woocommerce_order_items AS oi
                            WHERE 
                                oi.order_id = ".$orderID."
                                AND oi.order_item_type = 'coupon';
                        ");
                        
    if ($order_coupons) {
        $coupons = array_map(function($obj) {
            return $obj->coupon_code;
        }, $order_coupons);
        
        return implode(',', $coupons);
    } else {
        return "";
    }
}

function checkAdditionalItem($itemID) {
    $prefix = config('app.db_prefix');
    $has_serialized_data = DB::table($prefix . 'woocommerce_order_itemmeta')
                            ->where([
                                ['order_item_id', $itemID],
                                ['meta_key', '_ywapo_meta_data']
                            ])
                            ->first();

    if ($has_serialized_data) {
        return $has_serialized_data;
    } else {
        return false;
    }
}