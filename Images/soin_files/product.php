var sizeStock, wishlistSize, id, filterReviews, addToWishlist, removeToWishlist, initResume, initRangeColor, cookieLastView, init_add_birthlist, countdownManager;
var nbReviews = 10;

countdownManager = {
  // Configuration
  targetTime: new Date($('#countdown').data('date_end')), // Date cible du compte à rebours (00:00:00)
  displayElement: { // Elements HTML où sont affichés les informations
      day:  null,
      hour: null,
      min:  null,
      sec:  null
  },
   
  // Initialisation du compte à rebours (à appeler 1 fois au chargement de la page)
  init: function(){
      // Récupération des références vers les éléments pour l'affichage
      // La référence n'est récupérée qu'une seule fois à l'initialisation pour optimiser les performances
      this.displayElement.day  = $('#countdown_day');
      this.displayElement.hour = $('#countdown_hour');
      this.displayElement.min  = $('#countdown_min');
      this.displayElement.sec  = $('#countdown_sec');
       
      // Lancement du compte à rebours
      this.tick(); // Premier tick tout de suite
      window.setInterval("countdownManager.tick();", 1000); // Ticks suivant, répété toutes les secondes (1000 ms)
  },
   
  // Met à jour le compte à rebours (tic d'horloge)
  tick: function(){
      // Instant présent
      var timeNow = new Date();
      // On s'assure que le temps restant ne soit jamais négatif (ce qui est le cas dans le futur de targetTime)
      if( timeNow > this.targetTime ){
          timeNow = this.targetTime;
      }
       
      // Calcul du temps restant
      var diff = this.dateDiff(timeNow, this.targetTime);
       
      this.displayElement.day.text(  diff.day  );
      this.displayElement.hour.text( diff.hour );
      this.displayElement.min.text(  diff.min  );
      this.displayElement.sec.text(  diff.sec  );
  },
   
  // Calcul la différence entre 2 dates, en jour/heure/minute/seconde
  dateDiff: function(date1, date2){
      var diff = {}                           // Initialisation du retour
      var tmp = date2 - date1;

      tmp = Math.floor(tmp/1000);             // Nombre de secondes entre les 2 dates
      diff.sec = tmp % 60;                    // Extraction du nombre de secondes
      tmp = Math.floor((tmp-diff.sec)/60);    // Nombre de minutes (partie entière)
      diff.min = tmp % 60;                    // Extraction du nombre de minutes
      tmp = Math.floor((tmp-diff.min)/60);    // Nombre d'heures (entières)
      diff.hour = tmp % 24;                   // Extraction du nombre d'heures
      tmp = Math.floor((tmp-diff.hour)/24);   // Nombre de jours restants
      diff.day = tmp;

      return diff;
  }
};

$(function() {
  if ($('#countdown').length) {
    // Lancement du compte à rebours au chargement de la page
    countdownManager.init();
  }
  
  $('.productColor > a').click(function(){
    if ($('#filter_color').hasClass('oh')) {
      $('#filter_color').removeClass('oh');
      $('#filter_color').height('100%');
      $(this).text('Voir - de coloris');
    } else {
      $('#filter_color').addClass('oh');
      $('#filter_color').height('60px');
      $(this).text('Voir + de coloris');
    }
    return false;
  });
  // initialisation des coloris de gamme
  initRangeColor = function(){
    if ($('#filter_color').length && !$('#filter_color').hasClass('jsNoExpand')) {
      $('#filter_color').height('60px');
      $('#filter_color').addClass('oh');
      if (($('.productColor ul').children().length * $('.productColor ul > li:first').width()) > $('#filter_color').width()) {
        $('.productColor > a').removeClass('dn');
      } else {
        $('.productColor > a').addClass('dn');
      }
    }
  }
  initRangeColor();
  
  $(window).resize(function() {
    initRangeColor();
  });
  
  init_carousel();

  id = $('#jsProductId').text();

  var cloneAlwaysMore = $("article .evermore").clone(true);

  $('.productContainer').on('click', '.btnAvertStock', function() {
    openLayer('calc_avert_stock');
    if ($('#sizeSelection').length && $('#sizeSelection option:selected').attr('data-ref').length) {
      $('#form_avert_stock input[name="id"]').val($('#sizeSelection option:selected').attr('data-ref'));
    }
  });

  var dataLoad = '';
  if ($('#bonus').size()) {
    dataLoad = 'offers=' + $('#bonus').attr('data-ids');
  }
  if ($('#label').size()) {
    dataLoad += ((dataLoad.length) ? '&' : '') + 'labels=' + $('#label').attr('data-ids');
  }
  if (dataLoad.length) {
    dataLoad += '&action=load_offers';
    $.ajax({
      type: 'POST',
      url: './util/php/ajax/index.php?file=product_action',
      dataType: 'xml',
      data: dataLoad,
      success: function(data) {
        if ($(data).find('offers').text().length) {
          $('#bonus .productOffersContent').html($(data).find('offers').text());
          //$('#offercode a').attr('target','_blank');
        } else {
          $('#bonus').remove();
        }
        if ($(data).find('labels').text().length) {
          temp = $(data).find('labels').text();
          $('#label .productLabelsContent').html(temp);
        } else {
          $('#label').remove();
        }
        processLinks();
      }
    });
  }

  /*
  $('article').on('click', '.btnInStore', function() {
    if (!$(this).hasClass('btnAvertStock')) {
      var ref     = id;
      openLayer('calc_store_exclusive');
      $('#form_store_exclusive #ref').val(id);
    }
  });
  */

  sizeStock = function() {
    var idProduct     = id;
    var titleProduct  = '';
    if ($('#sizeSelection').size()) {

      var price = $('.price').html();
      price = price.replace('€', '');
      price = price.replace(',', '.');
      price = parseFloat(price);

      var noDispo = "stockKo";
      var soonDispo = "stockSoon";

      var noAvertStock = "";
      if (price >= 20) {
        noAvertStock = " btnAvertStock";
      }

      var val = $('#sizeSelection').val();
      var selector = false, tab, classDispo, selected;
      if (val == '0') {
        if ($('.productSize input[type=hidden][value*="stockOk"]').size()) {
          selector = $('.productSize input[type=hidden][value*="stockOk"]:first');
        } else {
          selector = $('.productSize input[type=hidden][value*="' + noDispo + '"]:last');
        }
        selected = $('#sizeSelection > option').eq($("#sizeSelection input[type=hidden]").index(selector));
      } else {
        if (val.length) {
          selector = $('#dispoTaille_' + val);
        } else {
          selector = $('.productSize input[type=hidden]').eq($('> option:selected', $('#sizeSelection')).index());
        }
        selected = $('#sizeSelection > option:selected');
      }
      if (selector) {
        idProduct = selector.attr('id').replace('dispoTaille_', '');
        if (idProduct.length) {
          titleProduct = ' (' + selected.text() + ')';
        }

        tab = selector.val().split('|');
        $('.productDatas .stock').removeClass('stockSoon stockOs stockOk stockSp stockKo stockSz stockMg productAlertStock').addClass(tab[1]).attr('title', tab[0]).html(tab[0]);
        classDispo = tab[1];
      } else {
        classDispo = noDispo;
      }
      if (classDispo == noDispo) {
        if (noAvertStock.length) {
          $('.btnAddToCart').removeClass('soonAvailable cart stokke notAvailable btnAvertStock').addClass('notAvailable' + noAvertStock);
        } else {
          $('.btnAddToCart').removeClass('soonAvailable cart stokke notAvailable btnAvertStock').addClass('notAvailable').prop("disabled", true);
        }
        $('.productAddToCart .qty').addClass('dn');
      } else if (classDispo == soonDispo) {
        if (noAvertStock.length) {
          $('.btnAddToCart').removeClass('soonAvailable cart stokke notAvailable btnAvertStock').addClass('soonAvailable' + noAvertStock);
        } else {
          $('.btnAddToCart').removeClass('soonAvailable cart stokke notAvailable btnAvertStock').addClass('soonAvailable').prop("disabled", true);
        }
        $('.productAddToCart .qty').addClass('dn');
      } else {
        $('.productAddToCart .qty').removeClass('dn');
        $('.btnAddToCart').removeClass('soonAvailable cart stokke notAvailable btnAvertStock').addClass('cart').prop("disabled", false);
      }
    }

    var nameProduct = $('h1 span').text();
    nameProduct = nameProduct.replace('"', '\"');

  };
  
  
  wishlistSize = function() {
    if ($('#sizeSelection').size()) {
      var ref = $( "#sizeSelection option:selected" ).data('ref')
      if (ref != undefined) {
        $.ajax({
          type: 'POST',
          url: '/util/php/ajax/index.php?file=wishlist_action',
          data: 'action=get_product_wishlist_state&id_article=' + ref + '&id_wishlist=',
          success: function(data) {
            var line = $(data).find('line');
            if (line.size()) {
              eval(line.text());
            }
            //$('.btnWishlist').removeClass('inWishlist').attr('title', $('.btnWishlist').attr('data-title1'));
            //executeCache("header");
          }
        });
      }
    }
  };
  
  cookieLastView = function() {
    var ref = id;
    $.ajax({
      type: 'POST',
      url: './util/php/ajax/index.php?file=footer_banner',
      data: 'action=load&ref='+ref,
      success: function(retour) {
      }
    });
  }

  sizeStock();
  wishlistSize();
  cookieLastView();

  $('article').on('change', '#sizeSelection', function() {
    sizeStock();
    wishlistSize();
  });

  var displayNextPrev = '';
  if ($('#carousel-product').hasClass('carouselitem-count1')) {
    displayNextPrev = ' dn';
  }

  $('.gallery').bootstrapGallery({
    closeBtnAttrs: {
      "class": "btn-close fi-icon fi-cross",
      "aria-hidden": "true"
    },
    btnPrevAttrs: {
      "class": "btn-prev fi-icon fi-chevron-left" + displayNextPrev
    },
    btnNextAttrs: {
      "class": "btn-next fi-icon fi-chevron-right" + displayNextPrev
    }
  });

  $('.rate_bar').each(function() {
    var obj = $(this).find('.progressBar');
    obj.progressbar({
      'max': parseInt($(this).attr('data-all')),
      'value': parseInt($(this).attr('data-nb'))
    });
    $(this).click(function() {
      filterReviews($(this).attr('id'));
    });
  });

  filterReviews = function(note) {
    if (!$('#' + note).hasClass('selected')) {
      $('.rate_bar').removeClass('selected');
      $('#' + note).addClass('selected');
      note = '.' + note;
      $('.review').hide().filter(note).slice(0, nbReviews).fadeIn();
    } else {
      $('#' + note).removeClass('selected');
      $('.review').hide().slice(0, nbReviews).fadeIn();
      note = '';
    }

    if (!$('.review' + note).not(':visible').size()) {
      $('#nextReviews').hide();
    } else {
      $('#nextReviews').show();
    }
  };

  $('#nextReviews').click(function() {
    var selected = $('.rate_bar.selected');
    var classReview = '';
    if (selected.size()) {
      classReview = '.' + selected.attr('id');
    }
    var reviews = $('.review' + classReview).not(':visible');
    if (reviews.size()) {
      reviews.slice(0, nbReviews).fadeIn('slow');
    }
    reviews = $('.review' + classReview).not(':visible');
    if (!reviews.size()) {
      $(this).hide();
    }
  });

  addToWishlist = function(ref, id_wishlist) {
    //if($('#customerHeader').hasClass('connected')) {
    if($('.block-user-authenticated').is(':visible')) {
      if(typeof id_wishlist === 'undefined') {
        id_wishlist = '';
      }
      $.ajax({
        type: 'POST',
        url: '/util/php/ajax/index.php?file=wishlist_action',
        data: 'action=add_product_wishlist&id_article=' + ref + '&id_wishlist=' + id_wishlist,
        success: function(data) {
          var line = $(data).find('line');
          if (line.size()) {
            eval(line.text());
          }
        }
      });
    } else {
      openLayer('add_wishlist');
    }
    
  };
  
  removeToWishlist = function(wishlist_product_id) {
    $.ajax({
      type: 'POST',
      url: '/util/php/ajax/index.php?file=wishlist_action',
      data: 'action=remove_product_wishlist&id=' + wishlist_product_id,
      success: function(data) {
        var line = $(data).find('line');
        if (line.size()) {
          eval(line.text());
        }
      }
    });
  };
  
  $(document).on('click', '#add_wishlist .continue', function() {
    $('#add_wishlist').modal('hide');
    if (!$('#loginBlockHideShow').length) {
      var url = $(location).attr('href')
      url = url.replace('https://www.allobebe.fr/', '');
      url = encodeURIComponent(url); 
      var referer = encodeURIComponent($(location).attr('href')); 
      $.ajax({
        type: 'POST',
        url: '/util/php/ajax/index.php?file=user_action',
        data: 'action=login_block&referer='+referer+'&url_return='+url,
        success: function(data) {
          var line = $(data).find('line');
          if (line.size()) {
            $('body > div.container').before(line.text()); 
            initValidate();
            $('#loginEmail').focus();
          }
        }
      });
    } else {
      $('#loginBlockHideShow').show();
      $('#loginEmail').focus();
    }
    $('#loginBlockHideShow .alert-danger').html('').addClass('dn'); 
    $('html, body').animate({scrollTop : 0},800);
  });
  
  $('article').on('click', '.productAddToCart .btnWishlist, .btnBirthlist', function() {
    $('#loginBlockHideShow').hide();
    if ($('#sizeSelection').length) {
      if ($('#sizeSelection').val() === "0") {
        openLayer('product_avert_size');
        return false;
      } else {
        var ref     = $( "#sizeSelection option:selected" ).data('ref');
        var tab     = ref.split('_');
        var refInit = tab[0];
      }
    } else {
      var ref     = $(this).attr('data-ga3');
      var refInit = ref;
    }
    
    //if($('#customerHeader').hasClass('connected')) {
    if($('.block-user-authenticated').is(':visible')) {
      
      if ($(this).hasClass('btnWishlist')) {
        if ($(this).hasClass('inWishlist')) {
          var wishlistProductId = $(this).attr('data-wishlist-product-id');
          if(wishlistProductId !== 'undefined') {
            removeToWishlist(wishlistProductId);
          }
        } else {
          if ( $('.productAddToCart .btnAddToCart').hasClass('stokke')) {
            stokkeWishlist = true;
            openStokke('');
          } else {
            addToWishlist(ref, $(this).attr('data-wishlist-id'));
          }
        }
      }else{
        
        param_layer = {id_product: ref};
        if ( $('.productAddToCart .btnAddToCart').hasClass('stokke')) {
          stokkeBirthlist = true;
          openStokke('');
        } else {
          openLayer('calc_wishlist');
        }
        
      }
    } else {
      $.ajax({
        type: 'POST',
        url: '/util/php/ajax/index.php?file=wishlist_action',
        data: 'action=memoryAdd_product_wishlist&type=' + (($(this).hasClass('btnBirthlist')) ? '1' : '0'),
        success: function(data) {
          param_layer = {id_product: ref};
          openLayer('add_wishlist');
        }
      });
    }
    
  });
  
  function animateIconSelectColor() {
    $('.select_color').find('i').animate({ paddingTop:"5px" }, 500).animate({ paddingTop:"0px" }, 500);
    window.setTimeout(function() { animateIconSelectColor() }, 1000)
  }
  
  function init_add_birthlist() {
    //alert('test');
  }
  
  
  if($('.select_color').length) {
    animateIconSelectColor();
  }
});
