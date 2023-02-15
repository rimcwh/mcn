
window.onload = async function ()
{
  await test_fake_jwt() ;
}

async function test_fake_jwt()
{
  let url = "/api/v1/test/test-fake-jwt" ;
  try
  {
    const response = await fetch (url, {
      method: "GET",
      headers: {
        "Content-type": "application/json; charset=UTF-8",
      }
    }) ;
    response_status = response.status ;
    const retrieve_body = await response.json () ;
    let retrieve_body_string = JSON.stringify(retrieve_body) ;
    let output ;
    output = "" ;
    output += retrieve_body_string ;
    document.getElementById("result").innerHTML = output ;
  }
  catch (error)
  {
    console.log ('Request Failed', error) ;
  }
}
