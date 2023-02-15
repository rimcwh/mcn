window . onload = function ()
{
  console.log ('we are here') ;
  global_space . set_selected_tab ("account-setting") ;
  
}

var global_space=(function(){
  var selected_tab = '' ; //shared variable available only inside your module
  var account_setting ;
  var edit_setting_flag = 0 ;
  var load_msg_timer ;

  return {
    get_selected_tab : function ()
    {
      return selected_tab ; // this function can access my_var
    },
    set_selected_tab : function (value)
    {
      selected_tab = value ; // this function can also access my_var
    }
  } ;

})() ;


/*var global_space = (function() {
    var my_var = 10; //shared variable available only inside your module

    function bar() { // this function not available outside your module
        alert(my_var); // this function can access my_var
    }

    return {
        set_selected_tab: function(value) {
            alert(value); // this function can access my_var
        },
        b_func: function() {
            alert(my_var); // this function can also access my_var
        }
    };

})();*/