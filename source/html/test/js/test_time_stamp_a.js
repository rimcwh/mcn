
window.onload = async function()
{
  await test_time_stamp_a() ;
}

async function test_time_stamp_a()
{
  let url = "/api/v1/test/test-time-stamp/a" ;
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
    output = "" ;
    for (const x in retrieve_body)
    {
      output += x + " -> " + retrieve_body [x] + "<br /><br />" ;
    }
    
    document.getElementById("result").innerHTML = output ;
  }
  catch (error)
  {
    console.log ('Request Failed', error) ;
  }
}
