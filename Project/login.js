document.getElementById("un").addEventListener("input", function() {
    let un = document.getElementById("un").value;
    var num= /[0-9]/;
    if (un.length ==0) {
        document.getElementById("test").innerHTML = "Username required!";
        document.getElementById("test").style.color = "red";
        event.preventDefault();
    }
    if (un.length < 4) {
        document.getElementById("test").innerHTML = "Username must be at least 4 characters!";
        document.getElementById("test").style.color = "red";
        event.preventDefault();
    } 
    if (num.test(un)) {
        document.getElementById("test").innerHTML = "";
        event.preventDefault();
    } else {
        document.getElementById("test").innerHTML = "Username must contain at least one number!";
        document.getElementById("test").style.color = "red";
        event.preventDefault();
    }
});