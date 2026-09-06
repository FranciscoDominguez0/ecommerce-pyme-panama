@props([
    'brand' => 'unknown',
    'class' => 'w-12 h-8',
])

@php
    $b = strtolower(trim((string) $brand));
@endphp

@if($b === 'visa')
    <!-- Visa -->
    <svg class="{{ $class }}" viewBox="0 0 48 32" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Visa">
        <rect width="48" height="32" rx="4" fill="#FFFFFF" stroke="#E2E8F0"/>
        <path d="M19.8 21.2H16.9L18.7 10.8H21.6L19.8 21.2ZM15.5 10.8L12.7 18L12.4 16.4C11.9 14.6 10.3 12.7 8.5 11.7L10.9 21.2H13.8L18 10.8H15.5ZM31.8 17.9C31.8 15.3 28 15.1 28 13.6C28 13.1 28.6 12.6 29.6 12.4C30.1 12.3 31.5 12.2 33.1 13L33.6 10.9C32.9 10.6 31.8 10.3 30.4 10.3C27.3 10.3 25.1 12 25.1 14.3C25.1 17.8 30.1 17.6 30.1 19.9C30.1 20.6 29.4 21 28.4 21C26.9 21 25.5 20.4 24.7 19.9L24.1 22.1C25.2 22.6 26.9 23 28.5 23C31.8 23 34 21.3 34 18.8L31.8 17.9ZM41.4 21.2H44L42.1 10.8H39.9C39.4 10.8 38.8 11.1 38.6 11.7L34.8 21.2H37.8L38.4 19.6H41.2L41.4 21.2ZM39.1 17.6L40.3 13.8L40.9 17.6H39.1ZM7.7 10.8H4.7L4.6 11C7 11.6 9 13.4 10 15.4L9.1 11.2C9 10.9 8.4 10.8 7.7 10.8Z" fill="#1434CB"/>
    </svg>
@elseif($b === 'mastercard')
    <!-- Mastercard -->
    <svg class="{{ $class }}" viewBox="0 0 48 32" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Mastercard">
        <rect width="48" height="32" rx="4" fill="#FFFFFF" stroke="#E2E8F0"/>
        <circle cx="19" cy="16" r="9" fill="#EB001B"/>
        <circle cx="29" cy="16" r="9" fill="#F79E1B"/>
        <path d="M24 9.6A8.99 8.99 0 0 0 20.6 16 8.99 8.99 0 0 0 24 22.4 8.99 8.99 0 0 0 27.4 16 8.99 8.99 0 0 0 24 9.6Z" fill="#FF5F00"/>
    </svg>
@elseif(in_array($b, ['amex', 'american_express', 'american express']))
    <!-- American Express -->
    <svg class="{{ $class }}" viewBox="0 0 48 32" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="American Express">
        <rect width="48" height="32" rx="4" fill="#016FD0"/>
        <rect x="3" y="3" width="42" height="26" rx="2" fill="none" stroke="#60B5F7" stroke-width="0.8"/>
        <g fill="#FFFFFF">
            <path d="M6 19.5L10 9.5H13L17 19.5H14.4L13.6 17.3H9.4L8.6 19.5H6ZM10.1 15.2H12.9L11.5 11.5L10.1 15.2Z"/>
            <path d="M17.5 19.5V9.5H20.6L22.8 15.2L25 9.5H28.1V19.5H25.8V13.3L23.7 18.4H21.9L19.8 13.3V19.5H17.5Z"/>
            <path d="M29 19.5V9.5H35.8V11.7H31.5V13.5H35.3V15.5H31.5V17.3H35.8V19.5H29Z"/>
            <path d="M36.5 19.5L39.7 14.5L36.8 9.5H39.6L41.3 12.6L43 9.5H45.7L42.8 14.5L46 19.5H43.2L41.3 16.2L39.3 19.5H36.5Z"/>
        </g>
    </svg>
@elseif($b === 'discover')
    <!-- Discover -->
    <svg class="{{ $class }}" viewBox="0 0 48 32" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Discover">
        <rect width="48" height="32" rx="4" fill="#FFFFFF" stroke="#E2E8F0"/>
        <path d="M8 12.2H11.2C13.6 12.2 15 13.5 15 16C15 18.5 13.6 19.8 11.2 19.8H8V12.2ZM10.2 18.2H11.1C12.3 18.2 13 17.4 13 16C13 14.6 12.3 13.8 11.1 13.8H10.2V18.2ZM16.5 12.2H18.5V19.8H16.5V12.2ZM19.8 18.5L20.8 17.2C21.6 17.9 22.6 18.4 23.6 18.4C24.4 18.4 24.8 18.1 24.8 17.6C24.8 17 24.2 16.8 23.2 16.5C21.4 16 20.2 15.4 20.2 13.9C20.2 12.7 21.3 12 22.8 12C23.9 12 24.9 12.4 25.7 12.9L24.8 14.3C24.1 13.8 23.4 13.6 22.7 13.6C22.1 13.6 21.8 13.8 21.8 14.2C21.8 14.7 22.4 14.9 23.3 15.2C25.1 15.7 26.4 16.3 26.4 17.8C26.4 19.1 25.2 20 23.5 20C22.1 20 20.8 19.4 19.8 18.5ZM35.4 19.8L33.3 12.2H35.4L36.6 17.2L37.8 12.2H39.8L37.7 19.8H35.4ZM41 12.2H45.5V13.8H42.9V15.2H45.2V16.7H42.9V18.2H45.6V19.8H41V12.2Z" fill="#231F20"/>
        <circle cx="30" cy="16" r="3.8" fill="#F36F21"/>
    </svg>
@elseif(in_array($b, ['diners', 'diners_club', 'diners club']))
    <!-- Diners Club -->
    <svg class="{{ $class }}" viewBox="0 0 48 32" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Diners Club">
        <rect width="48" height="32" rx="4" fill="#FFFFFF" stroke="#E2E8F0"/>
        <circle cx="24" cy="16" r="10" fill="#0079BE"/>
        <path d="M22.5 9.5C18.9 9.5 16 12.4 16 16C16 19.6 18.9 22.5 22.5 22.5V9.5Z" fill="white"/>
        <path d="M25.5 9.5V22.5C29.1 22.5 32 19.6 32 16C32 12.4 29.1 9.5 25.5 9.5Z" fill="white"/>
        <circle cx="24" cy="16" r="4.5" fill="#0079BE"/>
    </svg>
@elseif($b === 'jcb')
    <!-- JCB -->
    <svg class="{{ $class }}" viewBox="0 0 48 32" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="JCB">
        <rect width="48" height="32" rx="4" fill="#FFFFFF" stroke="#E2E8F0"/>
        <g transform="translate(10, 7)">
            <rect x="0" y="0" width="8" height="18" rx="2" fill="#0060A9"/>
            <rect x="9.5" y="0" width="8" height="18" rx="2" fill="#E60012"/>
            <rect x="19" y="0" width="8" height="18" rx="2" fill="#008837"/>
            <text x="4" y="13" font-size="7" font-weight="900" fill="white" text-anchor="middle" font-family="sans-serif">J</text>
            <text x="13.5" y="13" font-size="7" font-weight="900" fill="white" text-anchor="middle" font-family="sans-serif">C</text>
            <text x="23" y="13" font-size="7" font-weight="900" fill="white" text-anchor="middle" font-family="sans-serif">B</text>
        </g>
    </svg>
@elseif(in_array($b, ['unionpay', 'union_pay']))
    <!-- UnionPay -->
    <svg class="{{ $class }}" viewBox="0 0 48 32" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="UnionPay">
        <rect width="48" height="32" rx="4" fill="#FFFFFF" stroke="#E2E8F0"/>
        <g transform="translate(10, 6)">
            <rect x="0" y="0" width="8.5" height="15" rx="2" fill="#D91D26"/>
            <rect x="9.5" y="0" width="8.5" height="15" rx="2" fill="#00478B"/>
            <rect x="19" y="0" width="8.5" height="15" rx="2" fill="#007B5F"/>
        </g>
        <text x="24" y="27" font-size="5" font-weight="900" fill="#00478B" text-anchor="middle" font-family="sans-serif">UnionPay</text>
    </svg>
@else
    <!-- Tarjeta Genérica -->
    <svg class="{{ $class }}" viewBox="0 0 48 32" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Tarjeta de crédito">
        <rect width="48" height="32" rx="4" fill="#1E293B"/>
        <rect y="6" width="48" height="6" fill="#0F172A"/>
        <rect x="6" y="18" width="8" height="6" rx="1.5" fill="#F59E0B"/>
        <circle cx="36" cy="21" r="3" fill="#94A3B8" fill-opacity="0.6"/>
        <circle cx="41" cy="21" r="3" fill="#94A3B8" fill-opacity="0.4"/>
    </svg>
@endif
