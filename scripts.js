// my_files/assets/scripts.js

async function gfPost(url = '', data = {}) {
  // Υποστηρίζει JSON API. Επιστρέφει πάντα object (ακόμα και σε error).
  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(data),
      credentials: 'same-origin'
    });
    // 401 => όχι συνδεδεμένος
    if (res.status === 401) {
      return { ok: false, unauthorized: true, message: 'Απαιτείται σύνδεση' };
    }
    return await res.json();
  } catch (e) {
    console.error(e);
    return { ok: false, message: 'Σφάλμα δικτύου' };
  }
}

/* -------------------------------------------------------
 * ΚΑΛΑΘΙ
 * ----------------------------------------------------- */

async function gfAddToCart(productId, qty = 1, goToCart = true) {
  const r = await gfPost('cart_api.php', { action: 'add', product_id: productId, qty });
  if (r.unauthorized) {
    alert('Πρέπει να συνδεθείς για να προσθέσεις προϊόν στο καλάθι.');
    window.location.href = 'login.php';
    return;
  }
  if (!r.ok) {
    alert(r.message || 'Αποτυχία προσθήκης στο καλάθι.');
    return;
  }
  // ενημέρωσε badge (αν υπάρχει) και πήγαινε στο cart
  await gfRefreshCartBadge();
  if (goToCart) {
    window.location.href = 'cart.php';
  } else {
    alert(r.message || 'Προστέθηκε στο καλάθι');
  }
}

async function gfRefreshCartBadge() {
  // Αν υπάρχει badge στο header, ενημέρωσέ το από το API
  const badge = document.querySelector('#gf-cart-badge');
  if (!badge) return;
  const r = await gfPost('cart_api.php', { action: 'list' });
  if (r && r.ok && r.totals) {
    badge.textContent = r.totals.items || 0;
    badge.style.display = (r.totals.items > 0) ? 'inline-block' : 'none';
  }
}

/* -------------------------------------------------------
 * WISHLIST
 * ----------------------------------------------------- */

async function gfToggleWishlist(productId) {
  const r = await gfPost('wishlist_api.php', { action: 'toggle', product_id: productId });
  if (r.unauthorized) {
    alert('Πρέπει να συνδεθείς για να χρησιμοποιήσεις τη Wishlist.');
    window.location.href = 'login.php';
    return;
  }
  if (!r.ok) {
    alert(r.message || 'Σφάλμα wishlist');
    return;
  }
  alert(r.message || 'Ενημερώθηκε η λίστα αγαπημένων');
}

/* -------------------------------------------------------
 * AUTO INIT
 * ----------------------------------------------------- */

document.addEventListener('DOMContentLoaded', () => {
  // Προσπάθησε να ενημερώσεις το badge του καλαθιού στο load
  gfRefreshCartBadge().catch(()=>{});
});
