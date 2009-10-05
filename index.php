<?php
include('tdom.php');
tdom::init('config/tdom_config.inc.php');
$db = tdom::document('docbook');

#para crear un articulo o un libro en docbook
$book = $db->type('book');
#definiendo una etiqueta:
$book->base()->label = 'Test Book';

#creamos el bookinfo y le adjuntamos un autor:
$author = new db_author('Jesus', 'Lara');
$author->email('jesuslarag@gmail.com');
$info = $book->info();
$info->authorgroup()->add($author);
#adjunto el copyright:
$info->copyright('2009', 'Jesus Lara');
$book->chapter('c1', 'Example Chapter')->p('Example Paragraph');
$book->chapter('c2', 'Another Example Chapter')->p('Example Paragraph');
$book->preface('preface', 'Example Preface');
$book->part('Part I', 'Example Part')->partintro('Introduction to part section')->chapter('c3', 'Another Chapter')->p('Another example paragraph');
#bibliografia del documento
$biblio = $book->bibliography('Test Bibliography');
#agrego un item libro:
$libro = new db_biblioentry('PHP Design Patterns and Practice', 'Apress');
$libro->isbn('0-201-10088-6');
$libro->copyright(2007, 'Apress Inc.');
$libro->createAuthor('Charles', 'Zimman');
#agrego la biblioentry al libro:
$biblio->add($libro);

#podemos crear un item propio usando la API de tdom
$book->create('colophon')->attribute('status', 'draft')->p('This document is a draft');
$book->render();

#abrir un archivo docbook:
$book->open(TDOM_BASE . 'examples/docbook-intro.xml');
#cambio el autor del documento:
$book->info()->author('Jesus', 'Lara', 'jesuslarag@gmail.com');
#y lo muestro en pantalla:
$book->render();

#creamos un articulo:
$article = $db->type('article');
#y creamos el articleinfo
$inf = $article->info()->author('Jesus', 'Lara', 'jesuslarag@gmail.com');
$inf->keywords(array('document', 'test', 'example', 'docbook', 'tdom'));
$article->render();

?>