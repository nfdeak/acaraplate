@props(['url' => url('/'), 'currentUrl' => url()->current()])
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebApplication",
    "name": "Caffeine Calculator: How Much Is Too Much?",
    "description": "Free caffeine calculator: enter height and sensitivity to get a personalized daily caffeine limit.",
    "url": "{{ $currentUrl }}",
    "applicationCategory": "HealthApplication",
    "operatingSystem": "Any",
    "offers": {
        "@@type": "Offer",
        "price": "0",
        "priceCurrency": "USD"
    },
    "author": {
        "@@type": "Organization",
        "name": "Acara Plate",
        "url": "{{ $url }}"
    }
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "FAQPage",
    "mainEntity": [
        {
            "@@type": "Question",
            "name": "How much caffeine is safe per day?",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "For most healthy adults, up to 400 mg per day is a common reference point. This calculator adjusts that educational limit based on height as a body-size proxy and self-reported caffeine sensitivity."
            }
        },
        {
            "@@type": "Question",
            "name": "How does the caffeine calculator work?",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "The calculator starts with a common adult reference limit, adjusts it conservatively by height and sensitivity, then lowers it for optional context such as pregnancy or breastfeeding."
            }
        },
        {
            "@@type": "Question",
            "name": "Why does height matter for caffeine?",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "Height is used as a simple body-size proxy. It is not a medical measurement, but it helps the tool avoid giving the same caffeine limit to every person."
            }
        },
        {
            "@@type": "Question",
            "name": "Is this caffeine calculator a substitute for medical advice?",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "No. This tool provides educational guidance only. People who are pregnant, breastfeeding, trying to conceive, taking medications, or managing a health condition should follow clinician guidance."
            }
        },
        {
            "@@type": "Question",
            "name": "Does caffeine affect everyone the same way?",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "No. Height, sensitivity, sleep, medications, health conditions, pregnancy, and breastfeeding can all change how caffeine feels and how much is too much."
            }
        }
    ]
}
</script>
