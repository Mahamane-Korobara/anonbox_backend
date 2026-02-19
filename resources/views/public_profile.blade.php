<div class="card">
    @if($prompt)
    <h1>{{ $user->display_name }} demande :</h1>
    <p class="question-text">"{{ $prompt->question_text }}"</p>
    @else
    <h1>Envoyer un message anonyme à {{ $user->display_name }}</h1>
    @endif

    <form action="/api/messages" method="POST">
        <input type="hidden" name="prompt_id" value="{{ $prompt?->id }}">
        <input type="hidden" name="user_id" value="{{ $user->id }}">

        <textarea name="content" placeholder="Dis-moi n'importe quoi..."></textarea>
        <button type="submit">Envoyer</button>
    </form>
</div>