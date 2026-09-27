<?php
$next = 1;
$page = 'next page';
?>

@extends('layouts/products')
<h1>All Products</h1>
<p>Show all products...</p>
@includes('includes/product-details', ['name' => 'acme'])

@if($next)
<a href="{{ $next }}">{{ $page }}</a><br>
@endif
