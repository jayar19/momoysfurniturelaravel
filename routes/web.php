<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.index');

$pages = [
    'index' => 'pages.index',
    'login' => 'pages.login',
    'products' => 'pages.products',
    'cart' => 'pages.cart',
    'orders' => 'pages.orders',
    'order-chat' => 'pages.order-chat',
    'payment' => 'pages.payment',
    'payment-options' => 'pages.payment-options',
    'track-delivery' => 'pages.track-delivery',
    'ar-viewer' => 'pages.ar-viewer',
    'offers' => 'pages.offers',
    'services' => 'pages.services',
    'contact' => 'pages.contact',
    'delivery-info' => 'pages.delivery-info',
    'return-policy' => 'pages.return-policy',
    'check-admin' => 'pages.check-admin',
    'test-orders' => 'pages.test-orders',
    'admin/dashboard' => 'pages.admin.dashboard',
    'admin/add-product' => 'pages.admin.add-product',
    'admin/edit-product' => 'pages.admin.edit-product',
    'admin/manage-orders' => 'pages.admin.manage-orders',
    'admin/metric-detail' => 'pages.admin.metric-detail',
];

foreach ($pages as $uri => $view) {
    Route::view($uri, $view);
    Route::view($uri.'.html', $view);
}
