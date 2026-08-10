@extends('layouts.cliente')

@section('title', 'Mis Direcciones')

@push('styles')
<style>
    .ambient-shadow {
        box-shadow: 0px 4px 20px rgba(0, 35, 73, 0.05);
    }
    .ambient-shadow-hover:hover {
        box-shadow: 0px 12px 32px rgba(0, 35, 73, 0.12);
    }
</style>
@endpush

@section('content')
<x-cliente.perfil.layout active="direcciones">
    <livewire:gestion-direcciones />
</x-cliente.perfil.layout>
@endsection
