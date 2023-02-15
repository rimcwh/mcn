
window.onload = function ()
{
  if (localStorage.getItem("theme") === null)
  {
    localStorage.setItem("theme", "theme-light") ;
  }
  document.documentElement.className = localStorage.getItem("theme") ;
}

async function login()
{
  let input_value, filtered_value ;
  let filtered_account_id, filtered_password ;
  let url = "/api/v1/login" ;
  let vbody ;
  let response_status ;
  let jwt_decoded ;
  let jwt_header ;
  let json_payload ;
  input_value = document.getElementById("account_id").value ;
  filtered_value = input_value.replace(/[^\w.\-@]/g, '') ;
  filtered_value = filtered_value.substring(0, 50) ;
  filtered_account_id = filtered_value ;
  input_value = document.getElementById("password").value ;
  filtered_value = input_value.replace(/[^\w_-]/g, '') ;
  filtered_value = filtered_value.substring(0, 50) ;
  filtered_password = filtered_value ;
  vbody = {
    "account_id": filtered_account_id
    , "password": filtered_password
  }
  try
  {
    const response = await fetch (url, {
      method: "POST",
      headers: {
        //"Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "Content-type": "application/json; charset=UTF-8",
      },
      //body: 'account_id=' + encodeURIComponent (filtered_account_id) + '&password=' + encodeURIComponent (filtered_password),
      body: JSON.stringify (vbody)
    }) ;
    response_status = response.status ;
    const result = await response.json () ;
    let output ;
    let jwt_payload ;
    /*output = "result type:" + typeof result + "<br />" ;
    output += "result: " + result + "<br />" ;
    output += "status: " + response_status + "<br />" ;
    output += "result.To: " + result.To + "<br />" ;
    output += "hasOwnProperty (To): " + result.hasOwnProperty ('To') + "<br />" ;
    output += "message: " + result.message + "<br />" ;*/
    output = JSON.stringify (result) + "<br />" ;
    if (! result.hasOwnProperty ("Token"))
    {
      output += "has not Token<br />" ;
    }
    else
    {
      output += "has Token<br />" ;
    }
    if (result.status === "success")
    {
      let base64Url = result.jwt.split(".") [1] ;
      let base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/") ;

      json_payload = decodeURIComponent(atob(base64).split("").map(function(c){
        return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2) ;
      }).join("")) ;
      jwt_header = JSON.parse(window.atob(result.jwt.split(".")[0])) ;
      jwt_payload = JSON.parse(json_payload) ;
      output = "parse sn: " + jwt_payload.sn ;
      localStorage.setItem("user_id", jwt_payload.sn) ;

      sessionStorage.removeItem("csrf-token") ; // 刪除殘留資料
      var csrf_token ;
      csrf_token = response.headers.get("x-csrf-token") ;
      localStorage.setItem("csrf-token", csrf_token) ;

      window.location.replace("../lobby/lobby.html") ;
      return ;
    }

    output = result.message ;
    document.getElementById("result").innerHTML = output ;

    /*if (0 != result.result)
    {
      document.getElementById ("result") . innerHTML = "failed: <br />" + result.msg + "<br />" ;
    }
    else
    {
      document.getElementById ("result") . innerHTML = result.msg + "<br />" ;
    }*/
    
  } catch (error)
  {
    console.log ("Request Failed", error) ;
  }
}
