<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Acesso Negado',
            text: 'Você não tem permissão para aceder a esta página.',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'home.php';
        });
    </script>
</body>