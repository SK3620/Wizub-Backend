import openai
import sys
import json

# 引数を取得
api_key = sys.argv[1]
content = sys.argv[2]

# APIキーを設定
openai.api_key = api_key

def get_openai_response(content):
    response = openai.ChatCompletion.create(
        # model="gpt-3.5-turbo-0125",
        model="gpt-4o-mini",
        messages=[
            {
                "role": "system",
                "content": (
                    "あなたは英語と日本語のエキスパートです。前後の文脈を考慮して、英文を自然な日本語に翻訳し、翻訳する英文の個数に応じた(ID)を付けて、JSON形式で返却してください。"
                    "フォーマットは以下にしてください。(ID）はInt型です。\n"
                    '{"（ID）": "（日本語訳）, （ID）": "（日本語訳）"..., }'
                ),
            },
            {
                "role": "user",
                "content": content,
            },
        ],
    )
    return response.choices[0].message['content']

if __name__ == "__main__":
    content = sys.argv[2]
    response = get_openai_response(content)
    print(response)
