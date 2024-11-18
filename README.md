# WooCommerce Share Cart System

**Plugin Name:** WooCommerce Share Cart System  
**Description:** A custom WooCommerce share cart system that allows users to share their cart with others, view the shared cart, and continue shopping from the shared cart.  
**Version:** 1.0  
**Author:** Extralsc
**Text Domain:** extralsc-wsc  

## Overview

The **WooCommerce Share Cart System** plugin allows WooCommerce users to share their shopping carts with others, view shared carts, and continue the shopping process. This system enables customers to share their carts via a link, and recipients can then view and proceed to checkout with the items already in the cart. The plugin works by creating a unique cart for each user and storing it in the database, which can be accessed and shared through a unique URL.

This plugin provides a custom functionality where the user can share their cart with others, allowing them to access the same cart, and then proceed to checkout directly.

## Features

- **Cart Sharing**: Users can share their cart with others by generating a unique URL.
- **Shared Cart Display**: Users can view the shared cart's contents and proceed to checkout.
- **WooCommerce Integration**: The system seamlessly integrates with WooCommerce, automatically adding products to the WooCommerce cart.
- **Template Redirect**: The plugin uses a custom template for viewing shared carts.
- **API Endpoints**: Interact with shared cart data programmatically via custom API endpoints.

## How to Use

### Display Shared Cart

To display the shared cart, you can use the shortcode `[extralsc_wsc_cart]` in any page or post. You need to pass the `ctoken` as a URL parameter, e.g., `yourwebsite.com/shared-cart?ctoken=123`. The plugin will retrieve the cart's contents and display it with product details.

### Add to Cart and Checkout

When viewing the shared cart, you can proceed to checkout by pressing the "Proceed and Buy" button. This will add all the items in the shared cart to the WooCommerce cart and redirect you to the checkout page.

### Shortcode Example

You can use the following shortcode in your pages or posts:

```plaintext
[extralsc_wsc_cart]
```

This will display the contents of the shared cart, provided the `ctoken` is passed in the URL.

For example:
- URL: `https://yourwebsite.com/shared-cart?ctoken=123`
- Shortcode used: `[extralsc_wsc_cart]`

### Creating Shared Cart Links

To share a cart, you can generate the URL based on the cart's `ctoken`. The plugin allows users to access shared carts via a URL like:

```plaintext
https://yourwebsite.com/shared-cart?ctoken=123
```

Replace `123` with the actual cart ID that is being shared.

## API Endpoints

The **WooCommerce Share Cart System** also provides custom **API endpoints** that can be used to interact with the shared cart programmatically. This can be useful if you want to integrate cart sharing functionality with external systems or build custom workflows.

### Available API Endpoints

1. **Get Cart Data by Cart ID**
   - **Endpoint**: `/wp-json/extralsc-wsc/v1/cart/{ctoken}`
   - **Method**: `GET`
   - **Description**: Retrieve the details of a cart by its `ctoken`.
   - **Parameters**:
     - `ctoken`: The ID of the cart you want to retrieve.
   - **Response Example**:
     ```json
     {
       "ctoken": "123",
       "items": [
         {
           "product_id": "12345",
           "name": "Product Name",
           "quantity": 2,
           "price": "20.00",
           "total_price": "40.00",
           "image_url": "http://example.com/path-to-image.jpg"
         },
         {
           "product_id": "67890",
           "name": "Another Product",
           "quantity": 1,
           "price": "15.00",
           "total_price": "15.00",
           "image_url": "http://example.com/path-to-image.jpg"
         }
       ]
     }
     ```

2. **Create or Update Cart**
   - **Endpoint**: `/wp-json/extralsc-wsc/v1/cart`
   - **Method**: `POST`
   - **Description**: Create a new cart or update an existing cart by adding items to it.
   - **Parameters** (in JSON format):
     - `ctoken` (optional): If the cart already exists, specify the `ctoken` to update it.
     - `items`: An array of items to be added to the cart, each with the following properties:
       - `product_id`: The ID of the product to add.
       - `quantity`: The number of items to add.
   - **Example Request Body**:
     ```json
     {
       "ctoken": "123",
       "items": [
         {
           "product_id": "12345",
           "quantity": 2
         },
         {
           "product_id": "67890",
           "quantity": 1
         }
       ]
     }
     ```
   - **Response Example**:
     ```json
     {
       "status": "success",
       "message": "Cart updated successfully",
       "ctoken": "123"
     }
     ```

3. **Delete Cart**
   - **Endpoint**: `/wp-json/extralsc-wsc/v1/cart/{ctoken}`
   - **Method**: `DELETE`
   - **Description**: Delete the cart and all associated items.
   - **Parameters**:
     - `ctoken`: The ID of the cart to be deleted.
   - **Response Example**:
     ```json
     {
       "status": "success",
       "message": "Cart deleted successfully"
     }
     ```

### Authentication

- The API endpoints use **basic authentication** or **cookie authentication** depending on the WooCommerce settings for API access.
- For security, make sure to enable proper permissions and authentication for your API endpoints if you plan to use them publicly.

## Installation

1. **Install the Plugin**:
    - Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin via the WordPress admin dashboard by searching for "WooCommerce Share Cart System."
    
2. **Activate the Plugin**:
    - Go to the "Plugins" menu in the WordPress admin area and click "Activate" next to the **WooCommerce Share Cart System** plugin.

3. **Set Up the Plugin**:
    - No additional setup is required. The plugin automatically creates the necessary database tables during activation.
    - You can use the `[extralsc_wsc_cart]` shortcode to display shared carts on your website.

## Plugin Structure

This plugin consists of several key components:

- **Database Tables**: The plugin creates several tables in the database to store cart data, cart items, cart sharing information, and cart sessions.
  
- **Shortcode**: The `[extralsc_wsc_cart]` shortcode is used to display shared carts.

- **REST API**: The plugin uses custom REST API endpoints to interact with the cart data programmatically.

## Example Usage

Here’s how you can use the plugin:

1. **Share a Cart**: A user can share their cart by copying the URL with the `ctoken` parameter and sending it to another user.
2. **View and Proceed to Checkout**: The recipient of the shared cart can open the URL and view the cart's contents. They can then proceed to checkout with all the items already added to their WooCommerce cart.

## Troubleshooting

- **Cart Not Found**: If a shared cart is not found, make sure the correct `ctoken` is being passed in the URL.
  
- **Products Not Adding to Cart**: Ensure that the products exist and that the WooCommerce cart is properly initialized. If there is an issue with adding products, check that the product IDs are valid and the quantities are correctly set.

## Contribution

Feel free to fork the repository and submit issues or pull requests if you'd like to contribute. If you encounter any bugs or have suggestions for improvement, please open an issue in the GitHub repository.

## License

This plugin is licensed under the **GPL v3** license.
