# RedBox Pickup Environment Configuration

Add these environment variables to your `.env` file to enable RedBox pickup functionality:

```env
# RedBox Pickup Configuration (Fixed Settings)
REDBOX_ENABLED=true
REDBOX_API_KEY=your_redbox_api_key_here
REDBOX_API_BASE_URL=https://app.redboxsa.com/api/business/v1
REDBOX_API_HOST=https://app.redboxsa.com
REDBOX_API_TIMEOUT=30

# RedBox Map Configuration (Fixed Settings)
REDBOX_MAP_PROVIDER=apple
REDBOX_DEFAULT_LAT=24.7135517
REDBOX_DEFAULT_LNG=46.6752957
REDBOX_DEFAULT_ZOOM=10
REDBOX_SEARCH_RADIUS=100000000

# RedBox Language Configuration (Fixed Settings)
REDBOX_DEFAULT_LANGUAGE=en
```

## Configuration Details

### Fixed API Configuration
These settings are configured once and don't change:
- `REDBOX_ENABLED`: Set to `true` to enable RedBox pickup functionality
- `REDBOX_API_KEY`: Your RedBox API authentication key
- `REDBOX_API_BASE_URL`: RedBox API base URL (fixed endpoint)
- `REDBOX_API_HOST`: RedBox host URL (fixed host)
- `REDBOX_API_TIMEOUT`: API request timeout in seconds (fixed setting)

### Fixed Map Configuration
These settings are configured once and don't change:
- `REDBOX_MAP_PROVIDER`: Map provider to use (apple, google, leaflet) - currently supports Apple Maps
- `REDBOX_DEFAULT_LAT`: Default latitude for map center (Riyadh: 24.7135517)
- `REDBOX_DEFAULT_LNG`: Default longitude for map center (Riyadh: 46.6752957)
- `REDBOX_DEFAULT_ZOOM`: Default map zoom level (fixed setting)
- `REDBOX_SEARCH_RADIUS`: Search radius for pickup points in meters (fixed setting)

### Fixed Language Configuration
- `REDBOX_DEFAULT_LANGUAGE`: Default language for RedBox interface (en/ar)

## Dynamic Configuration (Not in .env)

The following settings are **fetched dynamically** and should NOT be set in environment variables:

### Shipping Method Settings (Fetched from WooCommerce)
- **Shipping costs** - Configured in WooCommerce admin
- **Method titles** - Set in WooCommerce shipping method settings
- **Method descriptions** - Set in WooCommerce shipping method settings
- **Tax status** - Configured in WooCommerce shipping method settings
- **Free shipping thresholds** - Use WooCommerce's built-in free shipping method

### Pickup Point Data (Fetched from RedBox API)
- **Available pickup points** - Fetched dynamically from RedBox API
- **Point locations** - Retrieved from RedBox API based on search
- **Point details** - Fetched from RedBox API when points are loaded
- **Operating hours** - Retrieved from RedBox API
- **Point status** - Fetched from RedBox API

### Map Data (Fetched Dynamically)
- **Apple Maps token** - Fetched from RedBox API when needed
- **Search results** - Retrieved from Apple Maps API
- **User location** - Detected by browser when permitted

## Getting Your RedBox API Key

1. Contact RedBox support to get your API credentials
2. They will provide you with an API key for authentication
3. Add the API key to your environment variables
4. Test the integration using the create order page

## Testing the Integration

1. Set `REDBOX_ENABLED=true` in your `.env` file
2. Add a valid `REDBOX_API_KEY`
3. Configure RedBox shipping method in WooCommerce admin
4. Go to the create order page
5. Select "RedBox Pickup Delivery" as the shipping method
6. Click "Select Pickup Point" to open the map
7. Choose a pickup point from the map or list
8. Complete the order creation

## Troubleshooting

- **Map not loading**: Check if Apple Maps script is loading correctly
- **No pickup points**: Verify your API key and RedBox service status
- **API errors**: Check the Laravel logs for detailed error messages
- **Token issues**: Ensure your RedBox API key has the correct permissions
- **Shipping costs not showing**: Verify RedBox shipping method is configured in WooCommerce admin 