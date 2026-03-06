UPDATE users
SET email = 'lucas@lrvweb.com.br',
    password_hash = '$2y$12$Mqc/7W/35HISwv6hDLk8ketdVpvILkupMa8ka5OBEAJz2J8ThKvOm'
WHERE email IN ('admin@local', 'lucas@lrvweb.com.br');
