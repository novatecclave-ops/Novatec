<?php
if (extension_loaded('mysqli')) {
    echo "<h1> ¡mysqli está cargado correctamente!</h1>";
} else {
    echo "<h1> mysqli NO está disponible.</h1>";
}
?>