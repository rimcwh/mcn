
window.onload = async function ()
{
  await btn_clicked() ;
}


async function btn_clicked()
{
  let url = "/test/read_header.php" ;
  let vbody ;
  try
  {
    const response = await fetch (url, {
      method: "GET",
      headers: {
        "Content-type": "application/json; charset=UTF-8"
        ,"arR": "gAn"
      },
    }) ;
    response_status = response.status ;
    const retrieve_body = await response.text () ;
    //let retrieve_body_string = JSON.stringify(retrieve_body) ;
    let output ;
    output = retrieve_body + "<br /><br />response header: " ;
    for (let [key, value] of response.headers) { 
      output += (`${key} : ${value}`) + "<br />" ;
    }
    //output += retrieve_body_string ;
    document.getElementById("result").innerHTML = output ;
  }
  catch (error)
  {
    console.log ('Request Failed', error) ;
  }
}
