
var executeCache, productMore;
var urlNext = '';
var nginx   = '1595923841';
var numHash = 0;
$(function() {
  
  productMore = function(nbPage) {
    if ($('#bt_show_next').length) {
      $('#bt_show_next').button('loading');
    }
    
    if (nbPage == 1) {
      var price_sort = $('#bt_show_next').attr('data-sort');
      window.location.hash = "p_" + numHash;
      numHash++;
      history.replaceState('data', '', window.location.href);
    } else {
      var price_sort = '';
    }
    var urlSuite = '&price_sort=' + price_sort;
    if ($('#bt_show_next').hasClass('factfinder')) {
      urlSuite = '&factfinder=1';
    }
    urlSuite += '&_=' + nginx + '&nbpage=' + nbPage;
    if (!urlNext.length && $('body').hasClass('catLevel0')) {
      urlNext = document.referrer;
    }
    $.ajax({
      cache: true,
      type: "GET",
      url: "/util/php/ajax/index.php?file=page_get_listing",
      data: 'url=' + encodeURIComponent(urlNext) + urlSuite,
      success: function(data){
        if ($('#bt_show_next').length) {
          $('#bt_show_next').button('reset');
        }
      },
      complete: function(xhr, status) {
        loadingListing = false;
      }
    });
  };

  // if (!urlNext.length) {
    // $("#bt_show_next").remove();
  // }
  if (urlNext.length) {
    $("#bt_show_next").removeClass("dn");
  }else{
    $("#bt_show_next").remove();
  }

  $('#productsList').on('click', '#bt_show_next', function() {
    if (urlNext.length) {
      productMore(1);
    }
  });
  executeCache = function(what) {
    var datas = "";
    if (what == 'all' || what == 'listingt2s' || what == 'listing' || what == 'first') {
      var products = new Array();
      var i = 0;
      datas+= '';
      $('li.notUpdated').each(function(){
        var id = $(this).attr('id').replace('prod_', '');
        products[i] = id;
        i++;
      });
      
      if (products.length) {
        datas+= "&data=" + JSON.stringify(products);
      }
      
      if (what != 'listingt2s') {
        var products_colors = new Array();
        var i = 0;
        $('.productColor #filter_color li:not(".current")').each(function(){  
          var id = $(this).attr('id').replace('color_', '');
          products_colors[i] = id;
          i++;
        });
        if (products_colors.length) {
          datas+= "&colors=" + JSON.stringify(products_colors);
        }
        if ($('.productContainer').size() && !$('.productContainer').hasClass('rangeColorContainer')) {
          datas+= '&ref=' + $('.productContainer').attr('id');
        }
        if ($('body').hasClass('wishlist')) {
          datas+= '&wishlist=1';
        }
        if (what == 'first') {
          if ($('.bottomBanner').length) {
            datas+= '&idbottombanner=' + $('.bottomBanner').attr('data-id');
          }
        }
      }
    }

    datas+= '&action=' + what;

    $.ajax({
      type: "POST",
      url: "/util/php/ajax/index.php?file=cache",
      data: datas,
      success: function(retour){
        eval(retour);
      }
    });
  };

  var hash = window.location.hash;
  var regHash = new RegExp('#p_[0-9]+$', 'g');
  if (regHash.test(hash)) {
    hash = hash.replace("#p_", "");
    numHash = parseInt(hash);
    productMore(numHash);
    numHash++;
  } else {
    numHash = 2;
    executeCache('first');
  }
});