
window.onload = function ()
{
  if (localStorage.getItem("theme") === null)
  {
    localStorage.setItem("theme", "theme-light") ;
  }
  document.documentElement.className = localStorage.getItem("theme") ;
}

function clicked_login_button()
{
  window.location.href = "login/login.html" ;
}

function clicked_register_button()
{
  window.location.href = "register/register.html" ;
}

function clicked_lobby_button()
{
  window.location.href = "lobby/lobby.html" ;
}
