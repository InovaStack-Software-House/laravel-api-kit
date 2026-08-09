<?php

declare(strict_types=1);

return [
    'auth' => [
        'invalid_credentials' => 'Las credenciales proporcionadas son incorrectas.',
        'verification_link_sent' => 'Enlace de verificación enviado.',
        'email_already_verified' => 'El correo electrónico ya está verificado.',
        'email_verified' => 'Correo electrónico verificado correctamente.',
        'invalid_verification_link' => 'Enlace de verificación inválido.',
        'password_reset_link_sent' => 'Si la cuenta existe, se ha enviado un enlace para restablecer la contraseña.',
        'password_reset_success' => 'Tu contraseña ha sido restablecida.',
        'password_reset_invalid_token' => 'Este token para restablecer la contraseña no es válido.',
        'password_reset_invalid_user' => 'No encontramos un usuario con esa dirección de correo electrónico.',
        'password_reset_throttled' => 'Espera antes de volver a intentarlo.',
        'password_reset_failed' => 'No se pudo restablecer la contraseña con los datos proporcionados.',
        'token_not_found' => 'Token no encontrado.',
    ],
    'errors' => [
        'unauthenticated' => 'No autenticado.',
        'forbidden' => 'Prohibido.',
        'not_found' => 'El recurso solicitado no pudo ser encontrado.',
        'server_error' => 'Ocurrió un error inesperado.',
        'too_many_requests' => 'Demasiadas solicitudes. Inténtalo de nuevo más tarde.',
        'validation_failed' => 'Los datos proporcionados no son válidos.',
        'https_required' => 'HTTPS es obligatorio para este endpoint.',
        'unsupported_media_type' => 'Tipo de contenido no compatible. Usa cuerpos de solicitud application/json.',
        'idempotency_key_invalid' => 'Formato de cabecera Idempotency-Key inválido.',
        'idempotency_key_conflict' => 'Idempotency-Key ya fue usado con un payload diferente.',
    ],
    'sunset' => [
        'endpoint_unavailable' => 'Este endpoint ha sido retirado y ya no está disponible.',
    ],
];
