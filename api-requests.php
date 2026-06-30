<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <button onclick="getusers()">Get users</button>
    <div id="posts"></div>


<script> 
    const xhr = new XMLHttpRequest(); xhr
    xhr.open('GET', 'https://jsonplaceholder.typicode.com/posts', true); xhr

    xhr.onload = function() {
        console.log(JSON.parse(xhr.responseText));
    }

    xhr.send();


    async function getUsers() {
        const response = await fetch('http://jsonplaceholder.typicode.com/posts', {

        })
        console.log(responhse.text);
        
    }
