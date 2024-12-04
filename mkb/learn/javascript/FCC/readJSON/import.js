console.log('-------- starting import.js ----------');
var results = null;
console.log("Now to attempt a simple import:");
try {
    // import data from './user.json';
    // console.log("imported data = ", data );
    results = "Source error in import statement: An import declaration can only be used at the top level of a module.ts(1473)";

} catch (error) {
    console.log("Error trying to import user.json: ", error);
    results = JSON.stringify(error);
}

document.getElementById("results2").innerHTML = results;
