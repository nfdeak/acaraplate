@if(isset($summaries) && $summaries->isNotEmpty())
## Previous Conversation Context

The following summaries capture key moments from earlier in this conversation. Use this to maintain continuity and remember what was discussed. Do not explicitly mention that you're reading summaries — let this context inform your responses naturally.

@foreach($summaries as $summary)
### Summary {{ $summary->sequence_number }}
*{{ $summary->created_at->format('M j, Y') }}*

{!! $summary->summary !!}

@if($summary->key_facts !== [])
**Key facts:** {!! implode(', ', $summary->key_facts) !!}
@endif

@if($loop->last && $summary->hasUnresolvedThreads())
**Still pending:** {!! implode(', ', $summary->unresolved_threads) !!}
@endif

@endforeach
@php
$allTopics = $summaries->flatMap(fn ($s) => $s->topics ?? [])->unique()->values()->all();
@endphp
@if($allTopics !== [])
---
**Recurring topics in this conversation:** {!! implode(', ', $allTopics) !!}
@endif

@endif
