<?php

declare(strict_types=1);

return [
    'auth' => [
        'invalid_credentials' => 'As credenciais fornecidas estão incorretas.',
        'verification_link_sent' => 'Link de verificação enviado.',
        'email_already_verified' => 'O e-mail já está verificado.',
        'email_verified' => 'E-mail verificado com sucesso.',
        'invalid_verification_link' => 'Link de verificação inválido.',
        'password_reset_link_sent' => 'Se a conta existir, um link de redefinição de senha foi enviado.',
        'password_reset_success' => 'Sua senha foi redefinida.',
        'password_reset_invalid_token' => 'Este token de redefinição de senha é inválido.',
        'password_reset_invalid_user' => 'Não encontramos um usuário com esse endereço de e-mail.',
        'password_reset_throttled' => 'Aguarde antes de tentar novamente.',
        'password_reset_failed' => 'Não foi possível redefinir a senha com os dados fornecidos.',
        'token_not_found' => 'Token não encontrado.',
    ],
    'errors' => [
        'unauthenticated' => 'Não autenticado.',
        'forbidden' => 'Proibido.',
        'not_found' => 'O recurso solicitado não pôde ser encontrado.',
        'server_error' => 'Ocorreu um erro inesperado.',
        'too_many_requests' => 'Muitas solicitações. Tente novamente mais tarde.',
        'validation_failed' => 'Os dados fornecidos são inválidos.',
        'https_required' => 'HTTPS é obrigatório para este endpoint.',
        'unsupported_media_type' => 'Tipo de mídia não suportado. Use corpos de requisição application/json.',
        'idempotency_key_invalid' => 'Formato de cabeçalho Idempotency-Key inválido.',
        'idempotency_key_conflict' => 'Idempotency-Key já foi usado com um payload diferente.',
    ],
    'sunset' => [
        'endpoint_unavailable' => 'Este endpoint foi descontinuado e não está mais disponível.',
    ],
];
