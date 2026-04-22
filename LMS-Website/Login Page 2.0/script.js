function showStudentLogin() {
  document.getElementById('selection').style.display = 'none';
  document.getElementById('studentLogin').style.display = 'block';
}

function showInstructorLogin() {
  document.getElementById('selection').style.display = 'none';
  document.getElementById('instructorLogin').style.display = 'block';
}

function backToSelection() {
  document.getElementById('studentLogin').style.display = 'none';
  document.getElementById('instructorLogin').style.display = 'none';
  document.getElementById('selection').style.display = 'block';
}

function login(role) {
  let username, password;

  if(role === 'student') {
    username = document.getElementById('studentUsername').value;
    password = document.getElementById('studentPassword').value;
  } else {
    username = document.getElementById('instructorUsername').value;
    password = document.getElementById('instructorPassword').value;
  }

  if(username && password) {
    document.getElementById('studentLogin').style.display = 'none';
    document.getElementById('instructorLogin').style.display = 'none';
    document.getElementById('dashboard').style.display = 'block';
  } else {
    alert('Please enter both username and password.');
  }
}
