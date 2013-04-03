source articles_delta
{
    type = xmlpipe
    xmlpipe_command = cat {source_path}/articles_delta.xml
}

source articles
{
    type = xmlpipe
    xmlpipe_command = cat {source_path}/articles.xml
}

index articles
{
        source                  = articles
        path                    = {data_path}/articles

        docinfo                 = extern
        mlock                   = 0
        morphology              = stem_ru, soundex
        min_word_len            = 1
        min_prefix_len          = 1
        enable_star             = 1
        html_strip              = 1
        preopen                 = 1

        #wordforms				= {source_path}/wordforms.txt
        charset_type 			= utf-8
        charset_table			= 0..9, A..Z->a..z, _, a..z, U+A8->U+E5, U+B8->U+E5, U+410..U+42F->U+430..U+44F, U+430..U+44F, U+0451->U+0435
}

index articles_delta : articles
{
    source                  = articles_delta
    path                    = {data_path}/articles_delta
}

#############################################################################
## searchd settings
#############################################################################

searchd
{
	listen = {host}:{port}:mysql41
	log					= {data_path}/deamon_log
	query_log			= {data_path}/query_log
	read_timeout		= 10
	max_children		= 100
	pid_file			= {data_path}/pid
	max_matches			= 2000
	seamless_rotate		= 1
	preopen_indexes		= 0
	unlink_old			= 1
	compat_sphinxql_magics = 0
}
# --eof--
