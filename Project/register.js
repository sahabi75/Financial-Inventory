
document.getElementById("fname").addEventListener("input", function (){
    let uname = document.getElementById("fname").value;

    if (fname.length === 0) {
        document.getElementById("fn").innerHTML = "First name is required";
        document.getElementById("fn").style.color = "red";
      } else if (fname.length < 2) {
        document.getElementById("fn").innerHTML = "First name must be at least 2 characters";
        document.getElementById("fn").style.color = "red";
      } else if (!/^[a-zA-Z]+$/.test(fname)) {
        document.getElementById("fn").innerHTML = "First name must contain only letters";
        document.getElementById("fn").style.color = "red";
      } else {
        document.getElementById("fn").innerHTML = "Valid first name";
        document.getElementById("fn").style.color = "green";
      } 
    
});


document.getElementById("un").addEventListener("input", function (){
    let uname = document.getElementById("un").value;

    if (uname.length == 0) {
        document.getElementById("test").innerHTML = "Username required";
        document.getElementById("test").style.color = "red";
        event.preventDefault();
        
      } else if (uname.length < 3) {
        document.getElementById("test").innerHTML = "Username must be atleat 3 words";
        document.getElementById("test").style.color = "red";
        event.preventDefault();
      } else {
        document.getElementById("test").innerHTML = "Okay"
      }
    
});

document.getElementById("pass").addEventListener("input", function (){
    let password= document.getElementById("pass").value;
    var letc= /[A-Z]/;
    var lets= /[a-z]/;
    var sp= /[!@#$%^&*(),.?":{}|<>]/;
    var num= /[0-9]/;
    if (letc.test(password)) {
        document.getElementById("cap").innerHTML = "✅ Capital letter present";
        document.getElementById("cap").className = "requirement valid";
    } else {
        document.getElementById("cap").innerHTML = "❌ Capital letter required";
        document.getElementById("cap").className = "requirement invalid";
        event.preventDefault();
        
    }
    if (lets.test(password)) {
        document.getElementById("sm").innerHTML = "✅ Small letter present";
        document.getElementById("sm").className = "requirement valid";
        event.preventDefault();
    } else {
        document.getElementById("sm").innerHTML = "❌ Small letter required";
        document.getElementById("sm").className = "requirement invalid";
        event.preventDefault();
        
    }
    if (sp.test(password)) {
        document.getElementById("sp").innerHTML = "✅ Special Character present";
        document.getElementById("sp").className = "requirement valid";
        event.preventDefault();
    } else {
        document.getElementById("sp").innerHTML = "❌ Special Character required";
        document.getElementById("sp").className = "requirement invalid";
        event.preventDefault();
    }
    if (num.test(password)) {
        document.getElementById("num").innerHTML = "✅ Number present";
        document.getElementById("num").className = "requirement valid";
        event.preventDefault();
    } else {
        document.getElementById("num").innerHTML = "❌ Number required";
        document.getElementById("num").className = "requirement invalid";
        event.preventDefault();
    }
    
});

document.getElementById("phone").addEventListener("input", function (){
     let phone = document.getElementById("phone").value.trim();
        const bdPhoneRegex = /^(?:\+880|880|0)(15|16|17|18|19)\d{8}$/;

      if (phone.length === 0) {
        document.getElementById("ph").innerHTML = "Need a phone number";
        document.getElementById("ph").style.color = "red";
        event.preventDefault();

      } else if (!bdPhoneRegex.test(phone)) {
        document.getElementById("ph").innerHTML = "Invalid Bangladeshi phone number";
        document.getElementById("ph").style.color = "red";
      } else {
        document.getElementById("ph").innerHTML = "Valid Bangladeshi number ✅";
        document.getElementById("ph").style.color = "green";
      }
    
});


