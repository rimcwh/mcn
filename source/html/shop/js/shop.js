
var global_space = (function ()
{
  var selected_tab = "" ; //shared variable available only inside your module
  var my_shop_selected_tab = "" ;
  var account_setting ;
  var edit_setting_flag = 0 ;
  var load_msg_timer ;
  var last_msg_id = 0 ;
  var books_list ;
  var book_detail ;
  var book_detail_number = 0 ;
  var shopping_cart_total_price = 0 ;
  var rr_patch_basic_info_api ; // rr for Request Result
  var basic_info ;
  var my_order_list ;
  var my_order_detail_number = 0 ;
  var my_order_detail_toggle = [] ;
  var my_shop_launched_books_list ;
  var my_shop_launched_book_detail ;
  var my_shop_launched_book_status_result ;
  var my_shop_orders_list ;
  var shopping_cart_rr_post_checkout_order_api ;
  var temp_result ;

  function bar ()
  { // this function not available outside your module
    alert (my_var) ; // this function can access my_var
  }

  return { // 這邊的左大括號不能寫在下一行，會報錯，後面的都不執行 = =|||
    get_selected_tab : function () {
      return selected_tab ; // this function can access my_var
    },
    set_selected_tab : function (value)
    {
      selected_tab = value ; // this function can also access my_var
    },
    get_my_shop_selected_tab : function () {
      return my_shop_selected_tab ; // this function can access my_var
    },
    set_my_shop_selected_tab : function (value)
    {
      my_shop_selected_tab = value ; // this function can also access my_var
    },
    get_last_msg_id : function () {
      return last_msg_id ; // this function can access my_var
    },
    set_last_msg_id : function (value) {
      last_msg_id = value ; // this function can access my_var
    },
    get_books_list : function () {
      return books_list ;
    },
    set_books_list : function (value) {
      books_list = value ; // this function can access my_var
    },
    get_book_detail : function () {
      return book_detail ;
    },
    set_book_detail : function (value) {
      book_detail = value ; // this function can access my_var
    },
    get_book_detail_number : function () {
      return book_detail_number ;
    },
    set_book_detail_number : function (value) {
      book_detail_number = value ; // this function can access my_var
    },
    get_shopping_cart_total_price : function () {
      return shopping_cart_total_price ;
    },
    set_shopping_cart_total_price : function (value) {
      shopping_cart_total_price = value ; // this function can access my_var
    },
    get_rr_patch_basic_info_api : function () {
      return rr_patch_basic_info_api ;
    },
    set_rr_patch_basic_info_api : function (value) {
      rr_patch_basic_info_api = value ;
    },
    get_basic_info : function () {
      return basic_info ;
    },
    set_basic_info : function (value) {
      basic_info = value ;
    },
    get_my_order_list : function () {
      return my_order_list ;
    },
    set_my_order_list : function (value) {
      my_order_list = value ;
    },
    get_my_order_detail_number : function () {
      return my_order_detail_number ;
    },
    set_my_order_detail_number : function (value) {
      my_order_detail_number = value ;
    },
    get_my_order_detail_toggle : function () {
      return my_order_detail_toggle ;
    },
    set_my_order_detail_toggle : function (value) {
      my_order_detail_toggle = value ;
    },
    get_my_shop_launched_books_list : function () {
      return my_shop_launched_books_list ;
    },
    set_my_shop_launched_books_list : function (value) {
      my_shop_launched_books_list = value ;
    },
    get_my_shop_launched_book_detail : function () {
      return my_shop_launched_book_detail ;
    },
    set_my_shop_launched_book_detail : function (value) {
      my_shop_launched_book_detail = value ;
    },
    get_my_shop_launched_book_status_result : function () {
      return my_shop_launched_book_status_result ;
    },
    set_my_shop_launched_book_status_result : function (value) {
      my_shop_launched_book_status_result = value ;
    },
    get_my_shop_orders_list : function () {
      return my_shop_orders_list ;
    },
    set_my_shop_orders_list : function (value) {
      my_shop_orders_list = value ;
    },
    get_shopping_cart_rr_post_checkout_order_api : function () {
      return shopping_cart_rr_post_checkout_order_api ;
    },
    set_shopping_cart_rr_post_checkout_order_api : function (value) {
      shopping_cart_rr_post_checkout_order_api = value ;
    },
    get_temp_result : function () {
      return temp_result ;
    },
    set_temp_result : function (value) {
      temp_result = value ;
    },
    esf_get : function ()
    {
      return edit_setting_flag ;
    },
    esf_set : function (value)
    {
      edit_setting_flag = alue ;
    },
    as_get : function ()
    {
      return account_setting ;
    },
    as_set : function (value)
    {
      account_setting = value ;
    },
    get_load_msg_timer : function ()
    {
      return load_msg_timer ;
    },
    set_load_msg_timer : function (value)
    {
      load_msg_timer = value ;
    }
  } ;
})() ;

window.onload = async function ()
{
  save_csrf_token() ;
  if (localStorage.getItem("theme") === null)
  {
    localStorage.setItem("theme", "theme-light") ;
  }
  document.documentElement.className = localStorage.getItem("theme") ;
  
  global_space.set_selected_tab("books-list") ;
  global_space.set_my_shop_selected_tab("checking-orders") ;
  
  document.getElementById("div-book-detail-overlay").addEventListener("click", function( e ){
    e = window.event || e; 
    if(this === e.target) {
      clicked_book_detail_close() ;
    }
  });

  document.getElementById("div-my-shop-launched-books-detail-overlay").addEventListener("click", function( e ){
    e = window.event || e; 
    if(this === e.target) {
      clicked_my_shop_launched_books_detail_edited_poparea_close() ;
    }
  });

  
  document.getElementById("div-shopping-cart-checkout-overlay").addEventListener("click", function( e ){
    e = window.event || e; 
    if(this === e.target) {
      document.getElementById("div-shopping-cart-checkout-overlay").style.display = "none" ;
    }
  });

  document.getElementById("div-my-shop-launched-books-detail-status-info-overlay").addEventListener("click", function( e ){
    e = window.event || e; 
    if(this === e.target) {
      document.getElementById("div-my-shop-launched-books-detail-status-info-overlay").style.display = "none" ;
    }
  });

  document.getElementById("div-my-shop-launched-books-updating-detail-status-info-overlay").addEventListener("click", function( e ){
    e = window.event || e; 
    if(this === e.target) {
      document.getElementById("div-my-shop-launched-books-updating-detail-status-info-overlay").style.display = "none" ;
    }
  });
  
  document.getElementById("div-my-order-status-info-overlay").addEventListener("click", function( e ){
    e = window.event || e; 
    if(this === e.target) {
      document.getElementById("div-my-order-status-info-overlay").style.display = "none" ;
    }
  });

  await clicked_books_list() ;
}

window.onbeforeunload = function(event)
{
  
}

function save_csrf_token()
{
  var token ;
  token = "" ;
  if (
    ! (sessionStorage.getItem("csrf-token") === null)
    &&
    (localStorage.getItem("csrf-token") === null)
  )
  {
    token = sessionStorage.getItem("csrf-token") ;
    sessionStorage.removeItem("csrf-token") ;
    if (! (token === ""))
    {
      write_csrf_token(token) ;
      document.getElementById("div-csrf-token").innerText = token ;
    }
  }
  return 1 ;
}

function resave_csrf_token(headers)
{
  var update ;
  var token ;
  /*var tm ;
  tm = "" ;
  headers.forEach(
    (value, key) => tm += key + ":" + value + "  type=" + typeof value + "\n"
  );
  alert(tm) ;*/
  if (! (headers.has("x-update-csrf-token")))
  {
    return ;
  }
  update = headers.get("x-update-csrf-token") ;
  if (! (update === "1"))
  {
    return ;
  }
  if (! (headers.has("x-new-csrf-token")))
  {
    return ;
  }
  token = headers.get("x-new-csrf-token") ;
  write_csrf_token(token) ;
  document.getElementById("div-csrf-token").innerText = token ;
}

function load_csrf_token()
{
  return localStorage.getItem("csrf-token") ;
}

function write_csrf_token(token)
{
  localStorage.setItem("csrf-token", token) ;
}

function transfer_csrf_token()
{
  
}

function nav_lobby()
{
  transfer_csrf_token() ;
  window.location.href = "../lobby/lobby.html" ;
}

