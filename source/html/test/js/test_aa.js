
async function btn_clicked()
{
  let url = "/api/v1/test/test-aa" ;
  let vbody ;
  let text_a, text_b, text_c ;
  text_a = document.getElementById("text-a").value ;
  text_b = document.getElementById("text-b").value ;
  text_c = document.getElementById("text-c").value ;
  vbody = {
    "text-a": text_a,
    "text-b": text_b,
    "text-c": text_c
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
    const retrieve_body = await response.text () ;
    let retrieve_body_string = JSON.stringify(retrieve_body) ;
    let output ;
    output = "" ;
    output += retrieve_body ;
    document.getElementById("result").innerHTML = output ;
  }
  catch (error)
  {
    console.log ('Request Failed', error) ;
  }
}
