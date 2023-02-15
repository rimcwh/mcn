
window.onload = async function()
{
  await test_temp() ;
}

async function test_temp()
{
  let url = "/api/v1/test/test-temp" ;
  try
  {
    const response = await fetch (url, {
      method: "GET",
      headers: {
        "Content-type": "application/json; charset=UTF-8",
      }
    }) ;
    response_status = response.status ;
    const retrieve_body = await response.json() ;
    let retrieve_body_string = JSON.stringify(retrieve_body) ;
    let output ;
    output = retrieve_body_string + "<br /><br />" ;
    
    let result = retrieve_body ;
    
    
    
    /*output += "player1_id type: " + typeof result ["player1_id"] + "<br /><br />" ;
    
    output += "player2_id type: " + typeof result ["player2_id"] + "<br /><br />" ;
    
    output += "player3_id type: " + typeof result ["player3_id"] + "<br /><br />" ;
    
    output += "player4_id type: " + typeof result ["player4_id"] + "<br /><br />" ;
    
    if (typeof result ["player1_id"] === "number")
    {
      output += "p1 is number" + "<br /><br />" ;
    }
    
    if (! ("object" === typeof result ["player1_id"]))
    {
      output += "p1 not object" + "<br /><br />" ;
    }
    
    if (typeof result ["player2_id"] === "number")
    {
      output += "p2 is number" + "<br /><br />" ;
    }
    
    if (! (typeof result ["player3_id"] === "number"))
    {
      output += "p3 not number" + "<br /><br />" ;
    }
    
    if (typeof result ["player3_id"] === "object")
    {
      output += "p3 is object" + "<br /><br />" ;
    }*/
    
    if (null === result ["account_id_null"])
    {
      output += "account_id_null is ===null" + "<br /><br />" ;
    }
    else
    {
      output += "account_id_null is not ===null" + "<br /><br />" ;
    }
    
    if (null === result ["account_id_7"])
    {
      output += "account_id_7 is ===null" + "<br /><br />" ;
    }
    else
    {
      output += "account_id_7 is not ===null" + "<br /><br />" ;
    }
    
    if (! (null === result ["account_id_8"]))
    {
      output += "account_id_8 is not ===null" + "<br /><br />" ;
    }
    else
    {
      output += "account_id_8 is ===null" + "<br /><br />" ;
    }
    
    output += "type 7: " + typeof result ["account_id_7"] + "<br /><br />" ;
    
    output += "type 8: " + typeof result ["account_id_8"] + "<br /><br />" ;
    
    output += "type n: " + typeof result ["account_id_null"] + "<br /><br />" ;
    
    if ("string" === typeof result ["account_id_7"])
    {
      output += "7 is string" + "<br /><br />" ;
    }
    else
    {
      output += "7 is not string" + "<br /><br />" ;
    }
    
    document.getElementById("result").innerHTML = output ;
  }
  catch (error)
  {
    console.log ('Request Failed', error) ;
  }
}
