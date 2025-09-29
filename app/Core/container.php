<?php


use DI\ContainerBuilder;

$builder = new ContainerBuilder();

// TODO enable for production
// $builder->enableCompilation(CACHE_PATH);
// $builder->writeProxiesToFile(true, CACHE_PATH);


return $builder->build();
?>