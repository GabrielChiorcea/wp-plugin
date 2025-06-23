# wp-plugin

trebuie sa fie instalat woo, ACF si Custom Post Type


Instalezi pluginul Custom Post Type UI.

În dashboard WP → CPT UI → Add/Edit Post Types → Completezi:

Post Type Slug: mlc_cupon

Plural Label: Cupoane

Singular Label: Cupon

Configurezi opțiunile (public false, show UI true, etc)



La ACF
 Adaugă câmpurile (Fields) în grupul nou
a) cod_cupon
Click pe „Add Field”

Field Label: Cod Cupon

Field Name: cod_cupon (se completează automat după Label)

Field Type: Text

Instrucțiuni (opțional): „Codul care va fi folosit de client pentru reducere”

Required: bifează dacă vrei să fie obligatoriu

Leave rest default

b) puncte_necesare
Click pe „Add Field”

Field Label: Puncte Necesare

Field Name: puncte_necesare

Field Type: Number

Instrucțiuni: „Numărul de puncte pe care clientul trebuie să le aibă ca să deblocheze cuponul”

Required: bifează dacă vrei să fie obligatoriu

Min Value: 0 (opțional)

Step: 1 (opțional)

c) descriere_scurta
Click pe „Add Field”

Field Label: Descriere Scurtă

Field Name: descriere_scurta

Field Type: Textarea

Instrucțiuni: „O descriere concisă a cuponului, ce oferă clientului informații despre reducere”

Required: opțional

