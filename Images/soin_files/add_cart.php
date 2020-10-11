var addStokke, sizeStock, openStokke, id, filterReviews, wishlistProductId, formlayer;
var stokkeWishlist = false;
var stokkeBirthlist = false;
$(function() {
  addstokke = function(test, crossSelling) {
    if (test) {
      if (stokkeBirthlist) {
        openLayer('calc_wishlist');
        stokkeBirthlist = false;
      } else if(stokkeWishlist) {
        // si c'est un ajout à la wishlist
        addToWishlist(id);
        stokkeWishlist = false;
      } else {
        var suffAction = '';
        if (crossSelling) {
          suffAction = '-crossSelling';
        }
        // si c'est un ajout au panier
        updateCart('update' + suffAction, id, ((!$('#qty_' + id).length) ? 1 : $('#qty_' + id).val()), '' , ((!$('#qty_' + id).length) ? 'item' : 'fiche'), '', wishlistProductId);
        
      }
    } else {
      alert("Comfirmez que vous avez vu la vidéo");
      stokkeWishlist = false;
      return;
    }
  };

  openStokke = function(crossSelling) {
    var newWin = window.open('http://media.stokke.com/tripptrapp/movie.aspx?' 
    + 'lang=fr'
    + '&RETURNSUCCESS=' + encodeURIComponent('https://www.allobebe.fr/returnStokke.html?crossSelling=' + crossSelling), null, 'fullscreen=no,titlebar=no,status=no,toolbar=no,menubar=no,location=no,scrollbars=yes,width=750,height=510');
  };

  $('body').on('click', '.btnAddToCart', function() {
    
    // fermer le mini item layer en cas d'ajouter au panier par ce dernier
    $('#mini_item_product').remove();
    
    // Pour detecter les ajout au panier depuis le panier (cross selling)
    var suffAction = '';
    if ($(this).attr('data-crossSelling')) {
      suffAction = '-crossSelling';
    }

    wishlistProductId = $(this).parents('.productsListItem').attr('data-wishlist-product-id');
    if(typeof wishlistProductId === 'undefined') {
      wishlistProductId = '';
    }
    if (!$(this).hasClass('btnAvertStock')) {
      if ($(this).hasClass('stokke')) {
        if ($(this).parents('li.productsListItem').length) {
          id = $(this).parents('li.productsListItem').attr('id').replace('prod_', '');
        } else {
          id = $('.productAddToCart').attr('id').replace('prod_', '');
        }
        openStokke(suffAction.length ? 1 : 0);
      } else {
        
        var sizetest;
        if ($(this).parents('li.productsListItem').length) {
          sizetest = $(this).parents('li.productsListItem ').find('.productSize').length;
        } else {
          sizetest = $('#sizeSelection').length;
        }
        
        if (sizetest) {
          if ($('#sizeSelection').val() == 0) {
            openLayer('product_avert_size');
            return false;
          } else {
            var ref     = $('#sizeSelection').val();
            var tab     = ref.split('_');
            var refInit = tab[0];
          }
        } else {
          var ref     = $(this).attr('data-ga3');
          var refInit = ref;
        }
        var qty = (!$('#qty_' + refInit).length) ? 1 : $('#qty_' + refInit).val();
        updateCart('update' + suffAction, ref, qty, '' , $(this).attr('data-where'), '', wishlistProductId);
      }
    }
  });
  
  $(document).on('click', '.modal#add_product .btnAddToCartInsurance', function() {
    
    $.ajax({
      type: 'POST',
      url: urlAjax,
      data: 'action=addInsurance&ref=' + $(this).attr('data-insurance') + '&qty=' + $(this).attr('data-insurance_qty') + '&where=' + $(this).attr('data-where') + '&idInsert=' + $(this).attr('data-idinsert'),
      success: function(retour) {
        eval(retour);
        // $('#calc_cc_error').remove();
        
      }
    });
  });
});