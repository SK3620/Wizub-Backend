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
from youtube_transcript_api import YouTubeTranscriptApi

# 引数を取得
video_id = sys.argv[1]
proxy = sys.argv[2]

def get_transcript(video_id, proxy):
    transcript_list = YouTubeTranscriptApi.list_transcripts(video_id)

    # 最初にenを取得し、それが存在しない場合はjaを取得する
    # transcript = transcript_list.find_transcript(['en', 'ja'])

    # 最初に手動作成された字幕を取得し、それが存在しない場合は自動生成された字幕を取得する
    transcript = transcript_list.find_transcript(['en'])

    translated_transcript = transcript.translate('ja')
    result = translated_transcript.fetch()
    return {
        '結果': result,
    }

    # ja_transcript = transcript_list.find_manually_created_transcript(['ja'])
    ja_transcript2 = transcript_list.find_generated_transcript(['ja'])
    return {
        'translation_languages': transcript.translation_languages,
    }

    # transcript_data = transcript.fetch()
    # if 'ja' in [lang['language_code'] for lang in transcript.translation_languages]:
    #     ja_transcript = transcript.translate('ja').fetch()
    #     for i in range(len(transcript_data)):
    #         transcript_data[i]['ja_subtitle'] = ja_transcript[i]['text']

    return {
        'video_id': transcript.video_id,
        'language': transcript.language,
        'language_code': transcript.language_code,
        'is_generated': transcript.is_generated,
        'is_translatable': transcript.is_translatable,
        'translation_languages': transcript.translation_languages,
        'ja_subtitle': ja_transcript,
    }

    # IDのカウンターを初期化
    id_counter = 1

    transcripts = []
    for i in range(0, len(transcript_data), 2):
        # 1つ目の要素を取得
        text1 = transcript_data[i]['text']
        start = transcript_data[i]['start']
        duration = transcript_data[i]['duration']

        # 2つ目の要素が存在するかを確認し、存在すれば取得
        if i + 1 < len(transcript_data):
            text2 = transcript_data[i + 1]['text']
            duration += transcript_data[i + 1]['duration']  # durationを加算
        else:
            text2 = ''

        # text1とtext2を結合して'text'に入れる
        combined_text = text1 + " " + text2

        # レスポンスのフォーマット調整
        transcripts.append({
            'id': id_counter, # 暫定で一意性を保証しておく DBへの保存時、プライマリーキーとして自動インクリメント
            'subtitle_id': id_counter, # それぞれの動画のトランスクリプトのID
            'en_subtitle': combined_text, # 英語字幕
            'ja_subtitle': '', # 日本語字幕 日本語字幕は取得しないため空
            'memo': '' , # 学習メモ
            'start': start, # 字幕表示開始時間
            'duration': duration # 字幕表示時間
        })

        # IDカウンターをインクリメント
        id_counter += 1

    return {'subtitles': transcripts}

if __name__ == "__main__":
    video_id = sys.argv[1]
    proxy = sys.argv[2]
    transcripts = get_transcript(video_id, proxy)
    print(json.dumps(transcripts, ensure_ascii=False))
