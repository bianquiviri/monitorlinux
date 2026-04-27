<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ServerMonitoringService;

class ServerInfoController extends Controller
{
    protected $monitor;

    public function __construct(ServerMonitoringService $monitor)
    {
        $this->monitor = $monitor;
    }

    public function index(Request $request)
    {
        $serverId = $request->query('server_id');
        $server = $serverId ? \App\Models\Server::find($serverId) : \App\Models\Server::where('is_local', true)->first();
        
        $data = $this->monitor->getServerStats($server);
        $data['servers'] = \App\Models\Server::all();
        $data['selected_server_id'] = $server->id;
        $data['selected_server_name'] = $server->name;
        
        return view('server.info', $data);
    }

    public function processes(Request $request)
    {
        $serverId = $request->query('server_id');
        $server = $serverId ? \App\Models\Server::find($serverId) : \App\Models\Server::where('is_local', true)->first();

        $data = $this->monitor->getServerStats($server);
        $data['servers'] = \App\Models\Server::all();
        $data['timestamp'] = now()->format('Y-m-d H:i:s');
        return response()->json($data);
    }

    public function updateServer(Request $request, $id)
    {
        $server = \App\Models\Server::findOrFail($id);
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

    public function deleteServer($id)
    {
        $server = \App\Models\Server::findOrFail($id);
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
        ]);

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