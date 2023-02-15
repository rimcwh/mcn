window.onload = function ()
{
  get_expired_time() ;
}

async function get_expired_time()
{
  let input_value, filtered_value ;
  let filtered_account_id, filtered_password ;
  let url = '/api/v1/test/test-show-jwt-expired-time' ;
  let vbody ;
  let response_status ;
  let jwt_decoded ;
  let jwt_header ;
  let json_payload ;
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
    let output ;
    output = "" ;
    output += "sn: " + retrieve_body.sn + "<br />" ;
    output += "rs: " + retrieve_body.rs + "<br />" ;
    output += "<br />" ;
    output += "iat: " + retrieve_body.iat_date + "<br />" ;
    output += "exp: " + retrieve_body.exp_date + "<br />" ;
    output += "<br />" ;
    output += "now: " + retrieve_body.now + "<br />" ;
    
    document.getElementById("re").innerHTML = output ;
    return ;
  } catch (error)
  {
    console.log ('Request Failed', error) ;
  }
}
