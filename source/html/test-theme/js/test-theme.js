
window . onload = function ()
{
  document . documentElement . className = "theme-light" ;
}

function switch_theme ()
{
  var c ;
  c = document . getElementById ("checkbox-switch-theme"). checked ;
  if (c === true)
  {
    document . documentElement . className = "theme-dark" ;
  }
  else
  {
    document . documentElement . className = "theme-light" ;
  }
}
