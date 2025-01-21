document.getElementById('search-teams').addEventListener('input', function () {
    const query = this.value;
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `search-teams.php?query=${query}`, true);
    xhr.onload = function () {
        if (this.status === 200) {
            document.querySelector('.team-list').innerHTML = this.responseText;
        }
    };
    xhr.send();
});
