<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ServerMonitoringService;
use Inertia\Inertia;

class ServerInfoController extends Controller
{
    protected $monitor;

    public function __construct(ServerMonitoringService $monitor)
    {
        $this->monitor = $monitor;
    }

    public function listServers()
    {
        $servers = \App\Models\Server::all();
        return Inertia::render('Server/Index', compact('servers'));
    }

    public function index(Request $request, $id)
    {
        try {
            $realId = \Illuminate\Support\Facades\Crypt::decryptString($id);
            $server = \App\Models\Server::findOrFail($realId);
        } catch (\Exception $e) {
            return redirect()->route('server.list')->with('error', 'ID de servidor no válido');
        }
        
        $data = $this->monitor->getServerStats($server);
        $data['servers'] = \App\Models\Server::all();
        $data['selected_server_id_encrypted'] = \Illuminate\Support\Facades\Crypt::encryptString($server->id);
        $data['selected_server_name'] = $server->name;
        
        return Inertia::render('Server/Info', $data);
    }

    public function processes(Request $request)
    {
        $encryptedId = $request->query('server_id');
        try {
            $realId = $encryptedId ? \Illuminate\Support\Facades\Crypt::decryptString($encryptedId) : null;
            $server = $realId ? \App\Models\Server::find($realId) : \App\Models\Server::where('is_local', true)->first();
        } catch (\Exception $e) {
            $server = \App\Models\Server::where('is_local', true)->first();
        }

        $data = $this->monitor->getServerStats($server);
        $data['servers'] = \App\Models\Server::all();
        $data['timestamp'] = now()->format('Y-m-d H:i:s');
        return response()->json($data);
    }

    public function updateServer(Request $request, $id)
    {
        $realId = \Illuminate\Support\Facades\Crypt::decryptString($id);
        $server = \App\Models\Server::findOrFail($realId);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip' => 'nullable|string|max:255',
            'ssh_user' => 'nullable|string|max:255',
            'ssh_password' => 'nullable|string|max:255',
            'ssh_port' => 'nullable|integer',
        ]);

        if (!empty($validated['ssh_password'])) {
            $validated['ssh_password'] = \Illuminate\Support\Facades\Crypt::encryptString($validated['ssh_password']);
        } else {
            unset($validated['ssh_password']);
        }

        $server->update($validated);
        return response()->json(['success' => true]);
    }

    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'ip' => 'required|string',
            'ssh_user' => 'required|string',
            'ssh_password' => 'nullable|string',
            'ssh_port' => 'required|integer',
            'ssh_key' => 'nullable|string',
        ]);

        // Create a temporary model instance for testing
        $server = new \App\Models\Server($validated);
        
        $success = $this->ssh->testConnection($server);

        return response()->json(['success' => $success]);
    }

    public function deleteServer($id)
    {
        $realId = \Illuminate\Support\Facades\Crypt::decryptString($id);
        $server = \App\Models\Server::findOrFail($realId);
        if ($server->is_local) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar el servidor local'], 403);
        }
        $server->delete();
        return response()->json(['success' => true]);
    }

    public function addServer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip' => 'nullable|string|max:255',
            'ssh_user' => 'nullable|string|max:255',
            'ssh_password' => 'nullable|string|max:255',
            'ssh_port' => 'nullable|integer',
            'ssh_key' => 'nullable|string',
        ]);

        if (empty($validated['ssh_port'])) {
            $validated['ssh_port'] = 22;
        }

        if (!empty($validated['ssh_password'])) {
            $validated['ssh_password'] = \Illuminate\Support\Facades\Crypt::encryptString($validated['ssh_password']);
        }

        \App\Models\Server::create($validated);

        return response()->json(['success' => true]);
    }

    public function manageContainer(Request $request)
    {
        $request->validate([
            'server_id' => 'required|integer',
            'id' => 'required|string',
            'action' => 'required|string|in:start,stop,restart'
        ]);

        $server = \App\Models\Server::findOrFail($request->server_id);
        $success = $this->monitor->manageContainer($server, $request->id, $request->action);

        return response()->json([
            'success' => $success,
            'message' => $success ? "Contenedor {$request->action} exitoso" : "Error al ejecutar {$request->action}"
        ]);
    }
}