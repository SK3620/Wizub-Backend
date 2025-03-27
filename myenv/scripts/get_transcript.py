# import sys
# import json
# from youtube_transcript_api import YouTubeTranscriptApi

# # 引数を取得
# video_id = sys.argv[1]
# proxy = sys.argv[2]

# def get_transcript(video_id, proxy):

#     # プロキシサーバーを仲介する
#     transcript_list = YouTubeTranscriptApi.list_transcripts(video_id, proxies = {
#         'http': proxy,
#         'https': proxy
#     })

#     # 英語の字幕を取得し保存
#     # transcript = transcript_list.find_transcript(['en'])
#     # transcript_data = transcript.fetch()

#     # IDのカウンターを初期化
#     id_counter = 1

#     transcripts = []
#     for i in range(0, len(transcript_data), 2):
#         # 1つ目の要素を取得
#         text1 = transcript_data[i]['text']
#         start = transcript_data[i]['start']
#         duration = transcript_data[i]['duration']

#         # 2つ目の要素が存在するかを確認し、存在すれば取得
#         if i + 1 < len(transcript_data):
#             text2 = transcript_data[i + 1]['text']
#             duration += transcript_data[i + 1]['duration']  # durationを加算
#         else:
#             text2 = ''

#         # text1とtext2を結合して'text'に入れる
#         combined_text = text1 + " " + text2

#         # レスポンスのフォーマット調整
#         transcripts.append({
#             'id': id_counter, # 暫定で一意性を保証しておく DBへの保存時、プライマリーキーとして自動インクリメント
#             'subtitle_id': id_counter, # それぞれの動画のトランスクリプトのID
#             'en_subtitle': combined_text, # 英語字幕
#             'ja_subtitle': '', # 日本語字幕 日本語字幕は取得しないため空
#             'memo': '' , # 学習メモ
#             'start': start, # 字幕表示開始時間
#             'duration': duration # 字幕表示時間
#         })

#         # IDカウンターをインクリメント
#         id_counter += 1

#     return {'subtitles': transcripts}

# if __name__ == "__main__":
#     video_id = sys.argv[1]
#     proxy = sys.argv[2]
#     transcripts = get_transcript(video_id, proxy)
#     print(json.dumps(transcripts, ensure_ascii=False))

import sys
import json
from youtube_transcript_api import (
    YouTubeTranscriptApi,
    NoTranscriptFound,
    NoTranscriptAvailable,
    NotTranslatable,
    CouldNotRetrieveTranscript,
    TranslationLanguageNotAvailable,
    TranscriptsDisabled
)

def find_best_transcript(transcript_list, language):
    """手動字幕があれば取得し、無ければ自動字幕を取得"""
    try:
        return transcript_list.find_manually_created_transcript([language])
    except NoTranscriptFound:
        try:
            return transcript_list.find_generated_transcript([language])
        except NoTranscriptFound:
            return None

def get_transcript(video_id):
    try:
        transcript_list = YouTubeTranscriptApi.list_transcripts(video_id)

        # 英語字幕の取得 （字幕ID：4PFq_9PobBc auto-generated取得, is_translatable = true, auto-translated）
        en_generated_transcript = transcript_list.find_generated_transcript(['en'])
        return en_generated_transcript.translate('ja').fetch()

        # 英語字幕の取得
        en_transcript = transcript_list.find_transcript(['en'])
        en_data = en_transcript.fetch()

        # 日本語字幕の取得
        if en_transcript.is_translatable and any(lang['language_code'] == 'ja' for lang in en_transcript.translation_languages):
            ja_data = en_transcript.translate('ja').fetch()
        else:
            ja_data = None

        # 日本語字幕の取得
        # ja_transcript = transcript_list.find_transcript(['ja'])
        # if ja_transcript:
        #     ja_data = ja_transcript.fetch()
        # elif en_transcript.is_translatable and any(lang['language_code'] == 'ja' for lang in en_transcript.translation_languages):
        #     ja_data = en_transcript.translate('ja').fetch()
        # else:
        #     ja_data = None

        # 字幕データの作成
        subtitles = [
            {
                'id': i + 1,
                'subtitle_id': i + 1,
                'en_subtitle': en['text'],
                'ja_subtitle': ja_data[i]['text'] if ja_data else '',
                'memo': '',
                'start': en['start'],
                'duration': en['duration']
            }
            for i, en in enumerate(en_data)
        ]

        return {'subtitles': subtitles}

    except TranscriptsDisabled:
        return {"error": "字幕が無効化されている動画です"}
    except (NoTranscriptFound, NoTranscriptAvailable, CouldNotRetrieveTranscript) as e:
        return {"error": str(e)}

if __name__ == "__main__":
    video_id = sys.argv[1]
    result = get_transcript(video_id)
    print(json.dumps(result, ensure_ascii=False))
