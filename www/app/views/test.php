<?php
    $scripture = simplexml_load_file('obadiah.xml');
    echo "<ul id="texts">n";
    foreach ($scripture as $verse):
        echo "<li><div class="title">",$verse->c,"</div><div class="artist">by ",$artist,"</div><time>",$date,"</time></li>n";
    endforeach;
    echo "</ul>";
?>