<?php
// Hämta varukorgsdata baserat på cart_id
$cart_id = get_query_var('extralsc_cart_id');
$cart_items = Extralsc_WSC_Cart_Item::get_items_by_cart_id($cart_id); // Ersätt med din metod för att hämta varor

if (!$cart_items) {
    echo '<p>Varukorgen finns inte eller är tom.</p>';
    return;
}

echo '<h2>Delad Varukorg</h2>';
echo '<ul>';
foreach ($cart_items as $item) {
    $product = wc_get_product($item->product_id);
    echo '<li>';
    echo '<strong>' . esc_html($product->get_name()) . '</strong> - ';
    echo 'Antal: ' . esc_html($item->quantity) . '<br>';
    echo 'Pris: ' . esc_html($item->total_price) . ' SEK';
    echo '</li>';
}
echo '</ul>';

// Knapp för att lägga till alla varor i WooCommerce-varukorgen
echo '<button id="extralsc_add_to_cart" data-cart-id="' . esc_attr($cart_id) . '">Nästa</button>';
?>

<script type="text/javascript">
document.getElementById('extralsc_add_to_cart').addEventListener('click', function() {
    let cartId = this.getAttribute('data-cart-id');
    fetch(`/wp-json/extralsc-wsc/v1/add-to-woocommerce-cart`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ cart_id: cartId }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/kassa';  // Omdirigera till WooCommerce-kassan
        } else {
            alert('Något gick fel: ' + data.message);
        }
    });
});
</script>
