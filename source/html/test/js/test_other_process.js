
window.onload = async function ()
{
  let url = "/api/v1/test/test-other-process" ;
  try
  {
    const response = await fetch (url, {
      method: "GET",
      headers: {
        "Content-type": "application/json; charset=UTF-8",
      },
    }) ;
    response_status = response.status ;
    const retrieve_body = await response.json () ;
    global_space.set_result(retrieve_body) ;
    let output ;
    output = JSON.stringify(retrieve_body) ;
    document.getElementById("result").innerHTML = output ;
  }
  catch (error)
  {
    console.log ('Request Failed', error) ;
  }
}

var global_space = (function()
{
  // shared variable
  // available only inside your module
  var result ;

  return {
    get_result : function ()
    {
      return result ;
    },
    set_result : function (value)
    {
      result = value ;
    }
  } ;
})() ;
