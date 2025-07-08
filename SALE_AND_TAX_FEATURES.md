# Sale and Tax Features

## Overview
The WooCommerce Order Dashboard now includes enhanced product pricing with sale detection and configurable tax calculation.

## Features

### 1. Sale Price Detection
- **Automatic Detection**: The system automatically detects when products or variations are on sale
- **Price Comparison**: Compares `_sale_price` with `_regular_price` to determine sale status
- **Visual Indicators**: Sale prices are displayed with strikethrough regular prices and green sale prices

### 2. Configurable Tax Rate
- **Default Rate**: 15% tax rate (configurable)
- **Configuration**: Set via `WOO_TAX_RATE` environment variable or config file
- **Calculation**: Tax is calculated on the current price (sale price if on sale, regular price otherwise)

### 3. Enhanced Product Search Results
Each product search result now includes:
- `regular_price`: Original product price
- `sale_price`: Sale price (if applicable)
- `current_price`: Active price (sale price if on sale, regular price otherwise)
- `is_on_sale`: Boolean indicating if product is on sale
- `tax_rate`: Applied tax rate (e.g., 0.15 for 15%)
- `tax_amount`: Calculated tax amount
- `price_with_tax`: Final price including tax

### 4. Frontend Display
- **Search Dropdown**: Shows sale prices with strikethrough regular prices
- **Tax Information**: Displays tax amount and total price with tax
- **Order Table**: Shows both base price and price with tax for each line item
- **Order Summary**: Uses prices with tax for calculations

## Configuration

### Environment Variable
```env
WOO_TAX_RATE=0.15
```

### Config File
```php
// config/woo-order-dashboard.php
'tax_rate' => env('WOO_TAX_RATE', 0.15), // 15% default tax rate
```

## Database Fields Used
The system reads these WooCommerce meta fields:
- `_regular_price`: Original product price
- `_sale_price`: Sale price (if set)
- `_price`: Current active price

## Example Output
```json
{
  "product_id": 123,
  "variation_id": 456,
  "name": "Sample Product",
  "sku": "SAMPLE-123",
  "price": 85.00,
  "regular_price": 100.00,
  "sale_price": 85.00,
  "is_on_sale": true,
  "tax_rate": 0.15,
  "tax_amount": 12.75,
  "price_with_tax": 97.75,
  "attributes": {
    "Color": "Red",
    "Size": "Large"
  }
}
```

## Frontend Display Examples

### Search Dropdown
```
Product Name
Color: Red, Size: Large
ID: 123 | Variation: 456 (SKU: SAMPLE-123)

$100.00                    ← Strikethrough regular price
$85.00                     ← Green sale price
+ Tax: $12.75              ← Tax amount
Total: $97.75              ← Final price with tax
```

### Order Table
```
Product Name
Color: Red, Size: Large
Base: $85.00 | With Tax: $97.75

$97.75  |  2  |  $195.50  |  ×
```

## Benefits
1. **Accurate Pricing**: Always shows the correct current price
2. **Sale Visibility**: Clear indication of sale items
3. **Tax Transparency**: Shows tax breakdown for customers
4. **Configurable**: Easy to adjust tax rates per environment
5. **Consistent**: Tax calculation applied uniformly across all products 