
window.onload = async function()
{
  await test_try() ;
}

async function test_try()
{
  let url = "/api/v1/test/test-try" ;
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
    //const retrieve_body = await response.text() ;
    let retrieve_body_string = JSON.stringify(retrieve_body) ;
    let output ;
    output = retrieve_body_string + "<br /><br />" ;
    //output = retrieve_body ;
    
    document.getElementById("result").innerHTML = output ;
  }
  catch (error)
  {
    console.log ('Request Failed', error) ;
  }
}
