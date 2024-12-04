console.log('-------- starting user.json.js ----------');
const userJson = 
{
    "id": 1,
    "name": "John Doe",
    "age": 12
}
;
console.log("userJson = ", userJson);
let p = document.getElementById('results1');
p.innerHTML = "<pre>" + JSON.stringify(userJson) + "</pre>";