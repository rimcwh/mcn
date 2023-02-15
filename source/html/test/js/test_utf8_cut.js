
async function btn_clicked()
{
  let url = "/api/v1/test/test_utf8_cut" ;
  let vbody ;
  let text_data ;
  text_data = document.getElementById("text-data").value ;
  vbody = {
    "data": text_data
  }
  try
  {
    const response = await fetch (url, {
      method: "POST",
      headers: {
        "Content-type": "application/json; charset=UTF-8",
      },
      body: JSON.stringify(vbody)
    }) ;
    response_status = response.status ;
    const retrieve_body = await response.json () ;
    let retrieve_body_string = JSON.stringify(retrieve_body) ;
    let output ;
    output = "" ;
    output += retrieve_body ["data"] ;
    output += "<br /><br />" ;
    output += "data_strlen: " + retrieve_body ["data_strlen"] ;
    output += "<br /><br />" ;
    output += retrieve_body_string ;
    document.getElementById("result").innerHTML = output ;
  }
  catch (error)
  {
    console.log ('Request Failed', error) ;
  }
}
