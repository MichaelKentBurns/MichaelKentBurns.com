console.log('---------------- starting index.js ---------------');
var results = undefined;

let enable = true;
if (enable == true) {
  try {  
    console.log('Next step is to fetch the user.json file.');
    fetch('./user.json',
        {
            mode: 'no-cors'
        }
    )
        .then((response) => response.json())
        .then((json) => {
            console.log("json=", json);
            results = JSON.stringify(json);
        });
    } catch(error) {
        console.log("Error trying to fetch user.json: ",error);
        results = JSON.stringify(error);
    }

document.getElementById("results3").innerHTML = results;
}
else
    console.log('fetch disabled.');
