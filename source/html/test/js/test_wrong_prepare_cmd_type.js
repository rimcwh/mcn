
async function getJSON ()
{
  let input_value, filtered_value ;
  let filtered_account_id, filtered_password ;
  let url = '/api/v1/test/test_wrong_prepare_cmd_type' ;
  let vbody ;
  let response_status ;
  input_value = document . getElementById ("account_id") . value ;
  filtered_value = input_value . replace (/[^\w_-]/g, '') ;
  filtered_value = filtered_value . substring (0, 50) ;
  filtered_account_id = filtered_value ;
  input_value = document . getElementById ("password") . value ;
  filtered_value = input_value . replace (/[^\w_-]/g, '') ;
  filtered_value = filtered_value . substring (0, 50) ;
  filtered_password = filtered_value ;
  vbody = {
    "account_id": filtered_account_id,
    "password": filtered_password
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
    /*output = "result type:" + typeof result + "<br />" ;
    output += "result: " + result + "<br />" ;
    output += "status: " + response_status + "<br />" ;
    output += "result.To: " + result.To + "<br />" ;
    output += "hasOwnProperty (To): " + result.hasOwnProperty ('To') + "<br />" ;
    output += "message: " + result.message + "<br />" ;*/
    output = JSON.stringify (result) + "<br />" ;
    if (! result.hasOwnProperty ('Token'))
    {
      output += 'has not Token<br />' ;
    }
    else
    {
      output += 'has Token<br />' ;
    }

    document.getElementById ("result") . innerHTML = output
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
    console.log ('Request Failed', error) ;
  }
}

