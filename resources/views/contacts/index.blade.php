<!DOCTYPE html>
<html>
<head>
    <title>Contatos</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header {
            background: white; padding: 20px; border-radius: 8px;
            margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex; justify-content: space-between; align-items: center;
        }
        .header h1 { margin: 0; }
        .header-actions { display: flex; gap: 12px; align-items: center; }
        .btn {
            padding: 8px 16px; border: none; border-radius: 4px;
            cursor: pointer; text-decoration: none; font-size: 14px; display: inline-block;
        }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-danger:hover { background-color: #c82333; }
        .btn-secondary { background-color: #6c757d; color: white; }
        .btn-secondary:hover { background-color: #5a6268; }

        .card {
            background: white; padding: 20px; border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;
        }

        /* Search bar */
        .search-form { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-form input[type="text"] {
            flex: 1; padding: 8px 12px; border: 1px solid #ddd;
            border-radius: 4px; font-size: 14px;
        }
        .search-form button {
            padding: 8px 20px; background: #007bff; color: white;
            border: none; border-radius: 4px; cursor: pointer; font-size: 14px;
        }
        .search-form button:hover { background: #0056b3; }
        .search-form a {
            padding: 8px 14px; background: #6c757d; color: white;
            border-radius: 4px; text-decoration: none; font-size: 14px;
        }

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: #f8f9fa; padding: 12px 14px; text-align: left;
            font-size: 13px; color: #555; border-bottom: 2px solid #dee2e6;
        }
        tbody td {
            padding: 12px 14px; border-bottom: 1px solid #dee2e6;
            font-size: 14px; color: #333; vertical-align: top;
        }
        tbody tr:hover { background: #f8f9fa; }
        .message-cell { max-width: 340px; white-space: pre-wrap; word-break: break-word; }
        .empty-row td { text-align: center; color: #888; padding: 40px; }

        /* Pagination */
        .pagination { display: flex; gap: 6px; justify-content: flex-end; margin-top: 16px; flex-wrap: wrap; }
        .pagination a, .pagination span {
            padding: 6px 12px; border-radius: 4px; font-size: 13px;
            text-decoration: none; border: 1px solid #dee2e6; color: #007bff; background: white;
        }
        .pagination a:hover { background: #e9ecef; }
        .pagination .active span {
            background: #007bff; color: white; border-color: #007bff;
        }
        .pagination .disabled span { color: #aaa; cursor: default; }

        .total-badge {
            background: #e9ecef; color: #555; padding: 3px 10px;
            border-radius: 20px; font-size: 13px;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div>
            <h1>Contatos</h1>
            <span class="total-badge">{{ $contacts->total() }} registro(s)</span>
        </div>
        <div class="header-actions">
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Dashboard</a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
        </div>
    </div>

    <div class="card">
        {{-- Search --}}
        <form method="GET" action="{{ route('contacts.index') }}" class="search-form">
            <input type="text" name="search" placeholder="Buscar por nome ou e-mail…"
                   value="{{ request('search') }}">
            <button type="submit">Buscar</button>
            @if(request('search'))
                <a href="{{ route('contacts.index') }}">Limpar</a>
            @endif
        </form>

        {{-- Table --}}
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
                    <th>Mensagem</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contacts as $contact)
                    <tr>
                        <td>{{ $contact->id }}</td>
                        <td>{{ $contact->name }}</td>
                        <td><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></td>
                        <td>{{ $contact->role ?? '—' }}</td>
                        <td class="message-cell">{{ $contact->message }}</td>
                        <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($contact->created_at)->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="6">Nenhum contato encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($contacts->hasPages())
            <div class="pagination">
                {{ $contacts->links() }}
            </div>
        @endif
    </div>

</div>
</body>
</html>
