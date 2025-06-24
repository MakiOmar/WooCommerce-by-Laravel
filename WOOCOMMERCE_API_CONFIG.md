# WooCommerce API Configuration

This document explains how to configure the WooCommerce REST API for order creation.

## Environment Variables

Add the following variables to your `.env` file:

```env
# WooCommerce API Configuration
WOO_API_ENABLED=false
WOO_SITE_URL=https://your-woocommerce-site.com
WOO_CONSUMER_KEY=ck_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
WOO_CONSUMER_SECRET=cs_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
WOO_API_VERSION=wc/v3
WOO_API_TIMEOUT=30
WOO_API_MAX_RETRIES=3
WOO_API_RETRY_DELAY=2
WOO_API_VERIFY_SSL=true
WOO_API_DEFAULT_CURRENCY=USD
```

## Getting WooCommerce API Credentials

1. **Log in to your WooCommerce admin panel**
2. **Go to WooCommerce → Settings → Advanced → REST API**
3. **Click "Add Key"**
4. **Fill in the details:**
   - Description: "Laravel Dashboard API"
   - User: Select an admin user
   - Permissions: "Read/Write"
5. **Click "Generate API Key"**
6. **Copy the Consumer Key and Consumer Secret**

## Testing the API Connection

Run the following command to test your API configuration:

```bash
php artisan woo:test-api
```

## Usage

### Default Behavior (Database Method)
By default, orders are created using direct database insertion. This is the current method and works well for most cases.

### API Method
To use the WooCommerce REST API for order creation:

1. Set `WOO_API_ENABLED=true` in your `.env` file
2. Configure the required API credentials
3. Test the connection using `php artisan woo:test-api`
4. Create orders normally - they will now use the API

**Note**: Payment method and order status are taken from the form submission, not from environment variables. This ensures that users can select the appropriate payment method and order status for each order.

## Benefits of Using the API

- **Better Integration**: Orders created via API trigger all WooCommerce hooks and filters
- **Automatic Updates**: WooCommerce will automatically update its internal caches and lookup tables
- **Plugin Compatibility**: Better compatibility with WooCommerce plugins and extensions
- **Validation**: WooCommerce validates the data before creating the order
- **Consistency**: Ensures data consistency with WooCommerce's expected format

## Benefits of Using Database Method

- **Faster**: Direct database insertion is faster than API calls
- **Offline Capability**: Works even if the WooCommerce site is temporarily unavailable
- **More Control**: Direct control over the database structure
- **No API Limits**: No rate limiting or API call restrictions

## Switching Between Methods

You can easily switch between methods by changing the `WOO_API_ENABLED` environment variable:

- `WOO_API_ENABLED=false` (default) - Uses database method
- `WOO_API_ENABLED=true` - Uses API method

## Troubleshooting

### API Connection Issues

1. **Check your site URL**: Make sure it's accessible and doesn't have a trailing slash
2. **Verify API credentials**: Ensure the consumer key and secret are correct
3. **Check permissions**: The API key should have "Read/Write" permissions
4. **SSL issues**: If using self-signed certificates, set `WOO_API_VERIFY_SSL=false`

### Common Error Messages

- **"Unable to connect to WooCommerce API"**: Check site URL and network connectivity
- **"Authentication failed"**: Verify consumer key and secret
- **"Permission denied"**: Check API key permissions
- **"Invalid endpoint"**: Verify API version (should be `wc/v3`)

## Security Considerations

- Keep your API credentials secure and never commit them to version control
- Use HTTPS for your WooCommerce site
- Regularly rotate your API keys
- Use the minimum required permissions for your API keys 