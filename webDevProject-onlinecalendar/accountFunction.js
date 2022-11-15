function checkState() {
    fetch("login_verify.php", {
        method: 'POST',
        headers: { 'content-type': 'application/json' }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            $("#hidden-token").val(data.token);
            $("#hidden-username").val(data.username);
            $("#hidden-id").val(data.userid);

            $("#myModal-log").hide();
            $("#modal-btn-log").html("Welcome to your calendar! " + data.username + " (id: " + data.userid + ")");   
            $("#logout-btn").attr('hidden', false);
        }
        else {
            $("#myModal-log").hide();
            $("#modal-btn-log").html("Register | Log In");   
            $("#logout-btn").attr('hidden', true);
            login = false;
        }
    })
    .catch(err => console.error(err));
}

document.addEventListener("DOMContentLoaded", checkState, false);

function register(event) {
    const regusername = encodeURIComponent(document.getElementById('regusername').value);
    const regpassword = encodeURIComponent(document.getElementById('regpassword').value);
    const data = {'reg_username': regusername, 'reg_password':regpassword};
    fetch("register.php", {
        method: 'POST',
        body: JSON.stringify(data),
        headers: { 'content-type': 'application/json' }
    })
    .then(function(response) {
        return response.json();
    })

    .then(function(data) {
        alert(data.success ? "Successfully Registered!" : `Your account failed to registered${data.message}`);
    })
    .catch(err => console.error(err));
}

document.getElementById("register_btn").addEventListener("click", register, false);

function login(event) {
    const username = encodeURIComponent(document.getElementById("username").value); 
    const password = encodeURIComponent(document.getElementById("password").value); 

    const data = { 'username': username, 'password': password };

    fetch("login.php", {
            method: 'POST',
            body: JSON.stringify(data),
            headers: { 'content-type': 'application/json' }
        })
        .then(data => data.json())
        .then(data=> {
            alert(data.success ? "You are logged in!" : `ERROR: Log in failed - ${data.message}`);

            if (data.success) {

                $("#hidden-token").val(data.token);
                $("#hidden-username").val(data.username);
                $("#hidden-id").val(data.userid);
                $("#myModal-log").hide();
                $("#modal-btn-log").html("Welcome to your calendar! " + data.username + " (id: " + data.userid + ")");   
                $("#logout-btn").attr('hidden', false);
            }
        })
        .catch(err => console.error(err));
}

document.getElementById("login_btn").addEventListener("click", login, false); 

function logout(event) {
    fetch("logout.php", {
            method: 'POST',
            headers: { 'content-type': 'application/json' }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            alert(data.success ? "You have logged out. Redirecting to Home Page" : `Log out failed: ${data.message}`);
            if (data.success) {
                let eventParent = $("#event-total");
                eventParent.html("");
                $("#hidden-token").val(data.token);
                $("#hidden-username").val(data.username);
                $("#hidden-id").val(data.userid);

                $("#myModal-log").hide();
                $("#modal-btn-log").html("Register | Log In");   
                $("#logout-btn").attr('hidden', true);
            }
        })
        .catch(err => console.error(err));
}

document.getElementById("logout-btn").addEventListener("click", logout, false); 

