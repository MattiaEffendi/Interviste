const API_HOST = 'http://localhost:8000';

document.getElementById('content').style.display = 'none';
document.getElementById('error').style.display = 'none';
document.getElementById('loader').style.display = 'block';

const updateData = () => {
    fetch(API_HOST + '/info').then(response => response.json()).then(res => {
        document.getElementById('content').style.display = 'block';
        document.getElementById('error').style.display = 'none';
        document.getElementById('version').innerHTML = res.version;
    }).catch(() => {
        document.getElementById('content').style.display = 'none';
        document.getElementById('error').style.display = 'block';
    }).finally(() => {
        document.getElementById('loader').style.display = 'none';
    });
}

setInterval(() => {
    updateData();
}, 1000 * 30);
