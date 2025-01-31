<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmailTemplateController extends Controller
{

    private $title;
    private $user;


    /**
     * Create a constrcut of class
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user(); // returns user
            return $next($request);
        });

        $this->title = __('titles.email_templates');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $emailTemplates = EmailTemplate::latest()->get();

        // Se abbiamo dei parametri nella query string, li mettiamo in sessione
        if (request()->has('message')) {
            session()->flash('message', request()->get('message'));
            session()->flash('alert-class', request()->get('alert-class', 'alert-info'));
        }

        return view('email-templates.index', compact('emailTemplates'), ['title' => $this->title]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('email-templates.create', ['title' => $this->title]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'min:3',
                    Rule::unique('email_templates')
                ],
                'subject' => 'required',
                'template' => [
                    'required',
                    Rule::unique('email_templates')
                ],
                'body' => 'required'
            ], [
                'name.required' => 'Il nome del template è obbligatorio',
                'name.min' => 'Il nome del template deve essere di almeno :min caratteri',
                'name.unique' => "Esiste già un template email con il nome ':input'",
                'template.required' => 'La selezione del template è obbligatoria',
                'template.unique' => 'Questo template è già stato utilizzato in un altro modello',
                'subject.required' => "L'oggetto è obbligatorio",
                'body.required' => 'Il contenuto è obbligatorio'
            ]);

            $template = new EmailTemplate($validated);
            $template->active = $request->active ?? true;

            if (!$template->save()) {
                throw new \Exception('Impossibile salvare il template nel database');
            }

            return response()->json([
                'success' => true,
                'template' => $template,
                'message' => 'Template email creato con successo'
            ]);

        } catch (ValidationException $e) {
            // Ora questo catch funzionerà correttamente
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
                'errors' => $e->errors(),
                'shouldRedirect' => true,  // Aggiungiamo questa flag

            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Si è verificato un errore durante la creazione del template',
                'errors' => ['general' => [$e->getMessage()]],
                'shouldRedirect' => true,  // Aggiungiamo questa flag
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(EmailTemplate $emailTemplate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */

    public function edit(EmailTemplate $emailTemplate)
    {
        // Assegniamo il template email alla variabile item per mantenere
        // coerenza con le altre parti dell'applicazione
        $item = $emailTemplate;

        // Verifichiamo solo se l'utente ha i permessi di amministrazione
        if ($this->user->hasRole('superadministrator|administrator')) {
            return view('email-templates.edit', [
                'title' => $this->title,
                'item' => $item
            ]);
        }

        // Se l'utente non ha i permessi necessari, restituiamo un errore 403
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'min:3',
                    Rule::unique('email_templates')->ignore($emailTemplate->id)
                ],
                'subject' => 'required',
                'template' => [
                    'required',
                    Rule::unique('email_templates')->ignore($emailTemplate->id)
                ],
                'body' => 'required'
            ], [
                'name.required' => 'Template name is required',
                'name.min' => 'Template name must be at least :min characters',
                'name.unique' => "A template with the name ':input' already exists",
                'template.required' => 'Template selection is required',
                'template.unique' => 'This template is already in use',
                'subject.required' => 'Subject is required',
                'body.required' => 'Content is required'
            ]);

            $emailTemplate->fill($validated);
            $emailTemplate->active = $request->active ?? true;

            if (!$emailTemplate->save()) {
                throw new \Exception('Unable to update template in database');
            }

            return response()->json([
                'success' => true,
                'template' => $emailTemplate,
                'message' => 'Email template updated successfully'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
                'errors' => $e->errors(),
                'shouldRedirect' => true,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the template',
                'errors' => ['general' => [$e->getMessage()]],
                'shouldRedirect' => true,
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        try {
            // Tentiamo di eliminare il template
            if (!$emailTemplate->delete()) {
                throw new \Exception('Unable to delete the email template');
            }

            // Se l'eliminazione ha successo, restituiamo una risposta JSON positiva
            // seguendo lo stesso pattern di store e update
            return response()->json([
                'success' => true,
                'message' => 'Email template deleted successfully',
                'shouldRedirect' => true  // Aggiungiamo la flag shouldRedirect come negli altri metodi
            ]);

        } catch (\Exception $e) {
            // In caso di errore, seguiamo lo stesso pattern di gestione degli errori
            // usato in store e update
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the template',
                'errors' => ['general' => [$e->getMessage()]],
                'shouldRedirect' => true,  // Manteniamo la coerenza con la flag shouldRedirect
            ], 422);
        }
    }
}
