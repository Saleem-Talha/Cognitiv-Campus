<?php
function encodeId($id) {
    return base64_encode($id); 
}

function decodeId($encodedId) {
    return base64_decode($encodedId);  
}
?>