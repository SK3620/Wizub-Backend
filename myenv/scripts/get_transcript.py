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

# 引数を取得
video_id = sys.argv[1]
proxy = sys.argv[2]

# 英語字幕を取得する
def fetch_en_transcript(transcript_list, language):
    try:
        return transcript_list.find_manually_created_transcript([language])
    except (NoTranscriptFound, TranscriptsDisabled, NoTranscriptAvailable, CouldNotRetrieveTranscript):
        try:
            return transcript_list.find_generated_transcript([language])
        except (NoTranscriptFound, TranscriptsDisabled, NoTranscriptAvailable, CouldNotRetrieveTranscript):
            try:
                return transcript_list.find_transcript([language])
            except (NoTranscriptFound, TranscriptsDisabled, NoTranscriptAvailable, CouldNotRetrieveTranscript):
                raise CouldNotRetrieveTranscript
    except Exception as e:
        print(f"翻訳中に予期しないエラーが発生しました: {e}")
        raise CouldNotRetrieveTranscript

# 日本語訳を取得する
def translate_transcript(en_transcript, target_language):
    try:
        if en_transcript.is_translatable and any(lang['language_code'] == target_language for lang in en_transcript.translation_languages):
            return en_transcript.translate(target_language).fetch()
        return None
    except NotTranslatable:
        print(f"エラー: 翻訳ができません: {e}")
        return None
    except TranslationLanguageNotAvailable:
        print(f"エラー: 翻訳言語が利用できません: {e}")
        return None
    except Exception as e:
        print(f"翻訳中に予期しないエラーが発生しました: {e}")
        return None

def get_transcript(video_id, proxy):
    try:
        # プロキシサーバーを仲介する
        transcript_list = YouTubeTranscriptApi.list_transcripts(video_id, proxies = {
            'http': proxy,
            'https': proxy
        })

        # テスト用
        # transcript_list = YouTubeTranscriptApi.list_transcripts(video_id)

        # 英語字幕の取得
        en_transcript = fetch_en_transcript(transcript_list, 'en')
        fetched_en_transcript = en_transcript.fetch()

        # 日本語訳の取得
        translated_ja_transcripts = translate_transcript(en_transcript, 'ja')

        # IDのカウンターを初期化
        id_counter = 1

        transcripts = []
        for i in range(0, len(fetched_en_transcript), 2):
            # 1つ目の要素を取得
            en_text_1 = fetched_en_transcript[i]['text']
            en_start = fetched_en_transcript[i]['start']
            en_duration = fetched_en_transcript[i]['duration']

            ja_text_1 = ''
            for translated_ja_transcript in translated_ja_transcripts:
                if translated_ja_transcript["start"] == en_start:
                    ja_text_1 = translated_ja_transcript["text"]
                    break

            # 2つ目の要素が存在するかを確認し、存在すれば取得
            if i + 1 < len(fetched_en_transcript):
                en_text_2 = fetched_en_transcript[i + 1]['text']
                en_start_2 = fetched_en_transcript[i + 1]['start']
                en_duration += fetched_en_transcript[i + 1]['duration']  # durationを加算

            ja_text_2 = ''  
            for translated_ja_transcript in translated_ja_transcripts:
                if translated_ja_transcript["start"] == en_start_2:
                    ja_text_2 = translated_ja_transcript["text"]
                    break

            # else:
            #     en_text_2 = ''
            #     ja_text_2 = ''

            # 英語字幕を結合して'combined_en_text'に入れる
            combined_en_text = (en_text_1 + " " + en_text_2) if en_text_1 and en_text_2 else (en_text_1 if en_text_1 else en_text_2)
            # 翻訳した日本語字幕を結合して'combined_ja_text'に入れる
            combined_ja_text = (ja_text_1 + " " + ja_text_2) if ja_text_1 and ja_text_2 else (ja_text_1 if ja_text_1 else ja_text_2)

            # レスポンスのフォーマット調整
            transcripts.append({
                'id': id_counter, # 暫定で一意性を保証しておく DBへの保存時、プライマリーキーとして自動インクリメント
                'subtitle_id': id_counter, # それぞれの動画のトランスクリプトのID
                'en_subtitle': combined_en_text, # 英語字幕
                'ja_subtitle': combined_ja_text, # 日本語字幕 日本語字幕は取得しないため空
                'memo': '' , # 学習メモ
                'start': en_start, # 字幕表示開始時間
                'duration': en_duration # 字幕表示時間
            })

            # IDカウンターをインクリメント
            id_counter += 1

        return {'subtitles': transcripts}

    except TranscriptsDisabled as e:
        print(f"エラー: 字幕が無効化されている動画です: {e}")
        raise TranscriptsDisabled
    except NoTranscriptFound as e:
        print(f"エラー: 字幕が見つかりませんでした: {e}")
        raise NoTranscriptFound
    except NoTranscriptAvailable as e:
        print(f"エラー: 字幕が利用できません: {e}")
        raise NoTranscriptAvailable
    except CouldNotRetrieveTranscript as e:
        print(f"エラー: 字幕の取得ができません: {e}")
        raise CouldNotRetrieveTranscript
    except TranslationLanguageNotAvailable as e:
        print(f"エラー: 翻訳言語が利用できません: {e}")
        raise TranslationLanguageNotAvailable
    except NotTranslatable as e:
        print(f"エラー: 翻訳ができません: {e}")
        raise NotTranslatable
    except Exception as e:
        print(f"予期しないエラーが発生しました: {e}")
        raise Exception

if __name__ == "__main__":
    video_id = sys.argv[1]
    proxy = sys.argv[2]
    transcripts = get_transcript(video_id, proxy)
    print(json.dumps(transcripts, ensure_ascii=False))

